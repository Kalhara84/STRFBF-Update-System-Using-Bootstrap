<?php
if (!isset($_SESSION)) {
    session_start();
}

// Handle AJAX requests here instead of using separate endpoint files.
if (isset($_POST['action'])) {
    if (empty($_SESSION['validated']) || empty($_SESSION['susdusd'])) {
        header('HTTP/1.1 401 Unauthorized');
        exit('Please sign in again.');
    }
    require_once dirname(__FILE__) . '/db.php';
    $action = $_POST['action'];

    if ($action === 'clear_session') {
// Clear the saved approval IDs from session
unset($_SESSION['saved_approval_ids']);
echo "Session cleared";
        exit;
    }

    if ($action === 'fetch_items') {
// Validate input
if (!isset($_POST['type'])) {
    die("<div class='alert alert-danger'>Type not received!</div>");
}

$supported_types = array('purchase', 'raw', 'vaccine', 'chemicals', 'chickboxes', 'fuel', 'chq', 'estimates', 'service', 'direct_payment');
if (!is_string($_POST['type']) || !in_array($_POST['type'], $supported_types, true)) {
    header('HTTP/1.1 422 Unprocessable Entity');
    exit('This approval category is not implemented in the available application files.');
}
$type = mysqli_real_escape_string($conn, $_POST['type']);
// Attachments are optional; missing archive storage must not hide orders.
$attachment_select = 'NULL AS logimage';
$attachment_join = '';
if ($type == 'estimates' || $type == 'service') {
    $attachment_check = mysqli_query($conn, 'SELECT loginhsno, logimage FROM ctecomco_strbf_log.logmast_inh LIMIT 0');
    if ($attachment_check) {
        mysqli_free_result($attachment_check);
        $attachment_select = 'approval_attachment.logimage';
        $attachment_join = 'LEFT JOIN ctecomco_strbf_log.logmast_inh AS approval_attachment ON inhsno = approval_attachment.loginhsno';
    }
}

// Get saved IDs from session
$saved_ids = isset($_SESSION['saved_approval_ids']) ? $_SESSION['saved_approval_ids'] : array();

// Extract numerical IDs based on the type suffix to include in the SQL query
$type_suffix = '';
if ($type == 'purchase' || $type == 'raw' || $type == 'vaccine' || $type == 'chemicals' || $type == 'chickboxes' || $type == 'fuel')
{
 $type_suffix = 'purchase';
}
elseif ($type == 'chq')
{
 $type_suffix = 'chq';
}
elseif ($type == 'estimates')
{
 $type_suffix = 'estm';
}
elseif ($type == 'service')
{
 $type_suffix = 'serv';
}
elseif ($type == 'direct_payment') { $type_suffix = 'dirpay'; }

$include_ids_arr = array();
foreach ($saved_ids as $sid) {
    $parts = explode('##', $sid);
    if (count($parts) == 2 && $parts[1] == $type_suffix) {
        $include_ids_arr[] = (int)$parts[0];
    }
}
// Create an IN clause string. Use '0' if empty to prevent SQL syntax errors.
$in_clause = count($include_ids_arr) > 0 ? implode(',', $include_ids_arr) : '0';

?>
<style>
    #imageModal {
        display: none;
        position: fixed;
        z-index: 9999;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.7);
        text-align: center;
    }
    #modalImage {
        display: inline-block;
        max-width: 90%;
        max-height: 90%;
        margin-top: 5%;
        border: 2px solid white;
        border-radius: 4px;
    }
    .close-modal {
        position: absolute;
        top: 20px;
        right: 30px;
        color: white;
        font-size: 28px;
        cursor: pointer;
    }
    .close-modal:hover {
        color: #ccc;
    }
    .row-highlighted {
        background-color:  #ffeea0 !important;
    }
    .search-hidden {
        display: none;
    }
    .separator-row {
        background-color: #f5f5f5;
        font-weight: 600;
        color: #666;
    }
    .separator-row td {
        padding: 10px 5px;
        font-size: 12px;
    }
    .search-result-row {
        background-color: #87c7ff !important;
    }
</style>
<script>
function showImage(src) {
    document.getElementById("modalImage").src = src;
    document.getElementById("imageModal").style.display = "block";
}
function closeModal() {
    document.getElementById("imageModal").style.display = "none";
}
window.onclick = function(event) {
    var modal = document.getElementById("imageModal");
    if (event.target == modal) {
        modal.style.display = "none";
    }
}

// checkbox to highlight row
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('approveBox')) {
        var checkbox = e.target;
        var row = checkbox.closest('tr');
        var isSearchResult = row.classList.contains('search-result-row');

        if (checkbox.checked) {
            row.classList.add('row-highlighted');
            if (isSearchResult) {
                row.classList.remove('search-result-row');
            }
        } else {
            row.classList.remove('row-highlighted');
            if (isSearchResult) {
                row.classList.add('search-result-row');
            }
        }
    }
}, false);

// Search functionality that keeps selected rows
function setupTableSearch(tableContainer) {
    const searchInput = document.createElement('input');
    searchInput.type = 'text';
    searchInput.className = 'form-control';
    searchInput.placeholder = 'Search table...';
    searchInput.style.marginBottom = '10px';

    // Store original row order
    const table = tableContainer.querySelector('table');
    const tbody = table.querySelector('tbody');
    const originalRows = Array.from(tbody.querySelectorAll('tr'));

    searchInput.addEventListener('keyup', function() {
        filterTable(tableContainer, this.value, originalRows);
    });

    tableContainer.parentNode.insertBefore(searchInput, tableContainer);
}

function filterTable(tableContainer, searchValue, originalRows) {
    const table = tableContainer.querySelector('table');
    const tbody = table.querySelector('tbody');
    const selectedRows = [];
    const searchResults = [];

    searchValue = searchValue.toLowerCase();

    originalRows.forEach(row => {
        if (row.classList.contains('separator-row')) return;

        const checkbox = row.querySelector('.approveBox');
        const isSelected = checkbox && checkbox.checked;

        if (isSelected) {
            selectedRows.push(row);
        } else {
            const text = row.textContent.toLowerCase();
            if (searchValue === '' || text.includes(searchValue)) {
                searchResults.push(row);
            }
        }
    });

    const fragment = document.createDocumentFragment();

    selectedRows.forEach(row => {
        fragment.appendChild(row.cloneNode(true));
    });

    if (searchValue !== '' && selectedRows.length > 0 && searchResults.length > 0) {
        const separator = document.createElement('tr');
        separator.className = 'separator-row';
        separator.innerHTML = '<td colspan="100%">Search Results Below</td>';
        fragment.appendChild(separator);
    }

    searchResults.forEach(row => {
        fragment.appendChild(row.cloneNode(true));
    });

    tbody.innerHTML = '';
    tbody.appendChild(fragment);

    tbody.querySelectorAll('.approveBox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const row = this.closest('tr');
            if (this.checked) {
                row.classList.add('row-highlighted');
            } else {
                row.classList.remove('row-highlighted');
            }
        });
    });
}

document.addEventListener('DOMContentLoaded', function() {
    // Highlighting for checked rows
    const checkboxes = document.querySelectorAll('.approveBox');
    checkboxes.forEach(checkbox => {
        if (checkbox.checked) {
            checkbox.closest('tr').classList.add('row-highlighted');
        }
    });
});
</script>
<div id="imageModal">
    <span class="close-modal" onclick="closeModal()">&times;</span>
    <img id="modalImage">
</div>
<?php

if ($type == 'purchase' || $type == 'raw' || $type == 'vaccine' || $type == 'chemicals' || $type == 'fuel' || $type == 'chickboxes')
{
 $te_inhpotypsno=9999;
 if($type == 'purchase')
 {
  $te_inhpotypsno=758;
 }
 if($type == 'raw')
 {
  $te_inhpotypsno=759;
 }
 if($type == 'vaccine')
 {
  $te_inhpotypsno=760;
 }
 if($type == 'fuel')
 {
  $te_inhpotypsno=793;
 }
 if($type == 'chemicals')
 {
  $te_inhpotypsno=761;
 }
 if($type == 'chickboxes')
 {
  $te_inhpotypsno=762;
 }



 // Query modified to include recently approved IDs
 $sql = "SELECT inhdno,inhddt,inhrem,supnam,locnam,stksno,stkdes,inlsno,inlcpr,inlqty,inldes,inhucdnew,inhmrqreqby FROM inltran
                left join inhtran on inlinhsno=inhsno
                left join supmast on inhsupsno=supsno
                left join locmast on inhlocsno=locsno
                left join stkmast on inlstksno=stksno

                WHERE inhtyp='21' and (inlappyn='' OR inlsno IN ($in_clause)) and inhpotypsno='$te_inhpotypsno'
                order by inhsno asc limit 500";
 //echo "$sql<br>";
 $res = mysqli_query($conn, $sql) or die("sql error inl : ".mysqli_error($conn));

 // Check SQL error
 if (!$res) {
     die("<b>SQL ERROR:</b> " . mysqli_error($conn) . "<br>Query: " . $sql);
 }

 // No Data
 if (mysqli_num_rows($res) == 0) {
     die("<div class='alert alert-warning'>No items found for type: <b>$type</b></div>");
 }

 // Table Output
 echo '
 <style>
 .table-container {
     width: 100%;
 }
 .table thead th {
     position: sticky;
     top: 0;
     background: #f8f9fa;
     z-index: 1;
     border-bottom: 2px solid #dee2e6;
     font-weight: 600;
     color: #333;
     padding: 12px 8px !important;
     font-size: 13px;
 }
 .table tbody tr {
     background-color: white;
     border-bottom: 1px solid #eee;
 }
 .table tbody td {
     padding: 10px 8px !important;
     font-size: 13px;
     vertical-align: middle;
 }
 .table tbody tr:hover {
     background-color: #fafafa;
 }
 </style>
 <div class="table-container">
 <table class="table table-bordered" style="margin-bottom: 0;">
     <thead>
         <tr>
             <th>Location</th>
             <th>Purchase Order Number</th>
             <th>Date</th>
             <th>Item</th>
             <th>Supplier</th>
             <th>Price</th>
             <th>Qty</th>
             <th>Details</th>
             <th>Requested By</th>
             <th>More</th>
             <th>Approve</th>
             <th>User</th>
         </tr>
     </thead>
     <tbody>
 ';
 while ($row = mysqli_fetch_assoc($res)) {
     $te_ =$row['inlsno'].'##'.'purchase';
     $is_saved = in_array($te_, $saved_ids);
     if ($is_saved) {
         $checkbox_html = '<span class="badge bg-success">✓ Approved</span>';
         $row_style = 'background-color: #d4edda;';
     } else {
         $checkbox_html = '<input type="checkbox" value="'.$te_.'" class="approveBox">';
         $row_style = '';
     }
     echo "
     <tr style='{$row_style}'>
         <td>{$row['locnam']}</td>
         <td>{$row['inhdno']}</td>
         <td>{$row['inhddt']}</td>
         <td>{$row['stkdes']}</td>
         <td>{$row['supnam']}</td>
         <td>{$row['inlcpr']}</td>
         <td>{$row['inlqty']}</td>
         <td>{$row['inhrem']} {$row['inldes']}</td>
         <td>{$row['inhmrqreqby']}</td>

         <td>
             <button onclick='showHistory({$row['stksno']})'
                     class='btn btn-info btn-sm'>More</button>
         </td>
         <td>
             $checkbox_html
         </td>
         <td>{$row['inhucdnew']}</td>
     </tr>
     ";
 }

 echo "    </tbody>
 </table>
 </div>";

}

if($type=='chq')
{

 // Query modified to include recently approved IDs
 $sql_grl = "
                 select grhsno,grhgrh,grhddt,grhdes1,grhdes2,
                 grhucdnew,grhentddt,grhsupref,grhchqno,supnam,
                 grlsno,grllno,grldc,grlamt,grlamtd,grlram,grlpen,grlunadjamt
                 from g_grhmast left join commast on grhcomsno=comsno
                                left join locmast on grhlocsno=locsno
                                left join g_sysmast on grhtypsno=syssno
                                left join g_grlmast on grlgrhsno=grhsno
                                left join supmast on grlsupsno=supsno
                 where grhcanucd='' and grhtypsno=727 and grlsupsno <>0 and (grhappyn!='y' OR grhsno IN ($in_clause))
                 order by grhddt,grhgrh";
 //echo "$sql_grl<br>";
 $result_grl = mysqli_query($conn, $sql_grl) or die("sql error grl : ".mysqli_error($conn));

 // Check SQL error
 if (!$result_grl) {
     die("<b>SQL ERROR:</b> " . mysqli_error($conn) . "<br>Query: " . $sql);
 }

 // No Data
 if (mysqli_num_rows($result_grl) == 0) {
     die("<div class='alert alert-warning'>No items found for type: </div>");
 }

 // Table Output
 echo '
 <style>
 .table-container {
     width: 100%;
 }
 .table thead th {
     position: sticky;
     top: 0;
     background: #f8f9fa;
     z-index: 1;
     border-bottom: 2px solid #dee2e6;
     font-weight: 600;
     color: #333;
     padding: 12px 8px !important;
     font-size: 13px;
 }
 .table tbody tr {
     background-color: white;
     border-bottom: 1px solid #eee;
 }
 .table tbody td {
     padding: 10px 8px !important;
     font-size: 13px;
     vertical-align: middle;
 }
 .table tbody tr:hover {
     background-color: #fafafa;
 }
 </style>
 <div class="table-container">
 <table class="table table-bordered" style="margin-bottom: 0;">
     <thead>
         <tr>
             <th>Voucher Number</th>
             <th>Cheque Number</th>
             <th>Supplier</th>
             <th>Date</th>
             <th>Value</th>
             <th>Details</th>
             <th>Approve</th>
             <th>User</th>
         </tr>
     </thead>
     <tbody>
 ';
 while ($row = mysqli_fetch_assoc($result_grl)) {
     $te_ =$row['grhsno'].'##'.'chq';
     $is_saved = in_array($te_, $saved_ids);
     $checkbox_html = $is_saved ?
         '<span class="badge bg-success">✓ Approved</span>' :
         '<input type="checkbox" value="'.$te_.'" class="approveBox">';
     $row_style = $is_saved ? 'background-color: #d4edda;' : '';
     echo "
     <tr style='{$row_style}'>
         <td>{$row['grhgrh']}</td>
         <td>{$row['grhchqno']}</td>
         <td>{$row['supnam']}</td>
         <td>{$row['grhddt']}</td>
         <td>{$row['grlamt']}</td>
         <td>{$row['grhdes1']} {$row['grhdes2']}</td>

         <td>
             $checkbox_html
         </td>
         <td>{$row['grhucdnew']}</td>

     </tr>
     ";
 }

 echo "    </tbody>
 </table>
 </div>";

}

if($type=='estimates')
{

 // Query modified to include recently approved IDs
 $sql_est = "SELECT inhdno, inhddt, inhestrefno, inhrem, supnam, locnam,
                    inlsno, inhsno, inlqty, inlcpr, inlval, inldes,
                    inhucdnew, inlappyn, $attachment_select
             FROM inltran
             LEFT JOIN inhtran ON inlinhsno=inhsno
             LEFT JOIN locmast ON inhlocsno=locsno
             LEFT JOIN supmast ON inhsupsno=supsno
             $attachment_join
             WHERE inhtyp=25 AND inhsupsno<>0 AND (inlappyn='' OR inlappyn IS NULL OR inhsno IN ($in_clause))
             ORDER BY inhdno ASC, inlsno DESC LIMIT 200";

 //echo "$sql_est<br>";
 $result_est = mysqli_query($conn, $sql_est) or die("sql error estimate : ".mysqli_error($conn));

 // Check SQL error
 if (!$result_est) {
     die("<b>SQL ERROR:</b> " . mysqli_error($conn) . "<br>Query: " . $sql_est);
 }

 // No Data
 if (mysqli_num_rows($result_est) == 0) {
     die("<div class='alert alert-warning'>No unapproved Documents found.</div>");
 }

 // Table Output
 echo '
 <style>
 .table-container {
     width: 100%;
 }
 .table thead th {
     position: sticky;
     top: 0;
     background: #f8f9fa;
     z-index: 1;
     border-bottom: 2px solid #dee2e6;
     font-weight: 600;
     color: #333;
     padding: 12px 8px !important;
     font-size: 13px;
 }
 .table tbody tr {
     background-color: white;
     border-bottom: 1px solid #eee;
 }
 .table tbody td {
     padding: 10px 8px !important;
     font-size: 13px;
     vertical-align: middle;
 }
 .table tbody tr:hover {
     background-color: #fafafa;
 }
 </style>
 <div class="table-container">
 <table class="table table-bordered" style="margin-bottom: 0;">
     <thead>
         <tr>
             <th>Location</th>
             <th>Ser. Odr No.</th>
             <th>Date</th>
             <th>Item Description</th>
             <th>Supplier</th>
             <th>Qty</th>
             <th>Price</th>
             <th>Total Value</th>
             <th>Remarks</th>
             <th>Approve</th>
             <th>User</th>
         </tr>
     </thead>
     <tbody>
 ';
 while ($row = mysqli_fetch_assoc($result_est)) {
    $te_ = $row['inhsno'].'##'.'estm';
    $is_saved = in_array($te_, $saved_ids);
    $checkbox_html = $is_saved ?
         '<span class="badge bg-success">✓ Approved</span>' :
         '<input type="checkbox" value="'.$te_.'" class="approveBox">';
    $row_style = $is_saved ? 'background-color: #d4edda;' : '';
    $imagePreview = '';
    if ($row['logimage']) {
        $imgSrc = 'data:image/jpeg;base64,' . base64_encode($row['logimage']);
        $imagePreview = '<img src="' . $imgSrc . '" alt="Item Image" width="100" style="cursor: pointer;" onclick="showImage(\'' . $imgSrc . '\')" />';
    }
     echo "
     <tr style='{$row_style}'>
         <td>{$row['locnam']}</td>
         <td>{$row['inhdno']}</td>
         <td>{$row['inhddt']}</td>
         <td>{$row['inldes']}</td>
         <td>$imagePreview</td>
         <td>{$row['supnam']}</td>
         <td>{$row['inlqty']}</td>
         <td>{$row['inlcpr']}</td>
         <td>{$row['inlval']}</td>
         <td>{$row['inhrem']}</td>

         <td>
             $checkbox_html
         </td>
         <td>{$row['inhucdnew']}</td>

     </tr>
     ";
 }

 echo "    </tbody>
 </table>
 </div>";

}

if($type=='service')
{

 // Query modified to include recently approved IDs
 $sql_est = "SELECT inhdno, inhddt, inhestrefno, inhrem, supnam, locnam,
                    inlsno, inhsno, inlqty, inlcpr, inlval, inldes,
                    inhucdnew, inlappyn, $attachment_select
             FROM inltran
             LEFT JOIN inhtran ON inlinhsno=inhsno
             LEFT JOIN locmast ON inhlocsno=locsno
             LEFT JOIN supmast ON inhsupsno=supsno
             $attachment_join
             WHERE inhtyp=28 AND inhsupsno<>0 AND (inlappyn='' OR inlappyn IS NULL OR inhsno IN ($in_clause))
             ORDER BY inhdno ASC, inlsno DESC LIMIT 250";

 //echo "$sql_est<br>";
 $result_est = mysqli_query($conn, $sql_est) or die("sql error estimate : ".mysqli_error($conn));

 // Check SQL error
 if (!$result_est) {
     die("<b>SQL ERROR:</b> " . mysqli_error($conn) . "<br>Query: " . $sql_est);
 }

 // No Data
 if (mysqli_num_rows($result_est) == 0) {
     die("<div class='alert alert-warning'>No unapproved Documents found.</div>");
 }

 // Table Output
 echo '
 <style>
 .table-container {
     width: 100%;
 }
 .table thead th {
     position: sticky;
     top: 0;
     background: #f8f9fa;
     z-index: 1;
     border-bottom: 2px solid #dee2e6;
     font-weight: 600;
     color: #333;
     padding: 12px 8px !important;
     font-size: 13px;
 }
 .table tbody tr {
     background-color: white;
     border-bottom: 1px solid #eee;
 }
 .table tbody td {
     padding: 10px 8px !important;
     font-size: 13px;
     vertical-align: middle;
 }
 .table tbody tr:hover {
     background-color: #fafafa;
 }
 </style>
 <div class="table-container">
 <table class="table table-bordered" style="margin-bottom: 0;">
     <thead>
         <tr>
             <th>Location</th>
             <th>Est No.</th>
             <th>Date</th>
             <th>Item Description</th>
             <th>Image</th>
             <th>Supplier</th>
             <th>Qty</th>
             <th>Price</th>
             <th>Total Value</th>
             <th>Remarks</th>
             <th>Approve</th>
             <th>User</th>
         </tr>
     </thead>
     <tbody>
 ';
 while ($row = mysqli_fetch_assoc($result_est)) {
    $te_ = $row['inhsno'].'##'.'serv';
    $is_saved = in_array($te_, $saved_ids);
    $checkbox_html = $is_saved ?
         '<span class="badge bg-success">✓ Approved</span>' :
         '<input type="checkbox" value="'.$te_.'" class="approveBox">';
    $row_style = $is_saved ? 'background-color: #d4edda;' : '';
    $pdfPreview = '';
    if ($row['logimage']) {
        $pdfUrl = 'data:application/pdf;base64,' . base64_encode($row['logimage']);
        $pdfPreview = "<input type='button' value='Preview' onclick=\"window.open('" . $pdfUrl . "', '_blank')\" class='button' />";
    }
     echo "
     <tr style='{$row_style}'>
         <td>{$row['locnam']}</td>
         <td>{$row['inhdno']}</td>
         <td>{$row['inhddt']}</td>
         <td>{$row['inldes']}</td>
         <td>$pdfPreview</td>
         <td>{$row['supnam']}</td>
         <td>{$row['inlqty']}</td>
         <td>{$row['inlcpr']}</td>
         <td>{$row['inlval']}</td>
         <td>{$row['inhrem']}</td>

         <td>
             $checkbox_html
         </td>
         <td>{$row['inhucdnew']}</td>

     </tr>
     ";
 }

 echo "    </tbody>
 </table>
 </div>";

}

// if($type=='raw')
// {

//  // Query modified to include recently approved IDs
//  $sql = "SELECT inhdno,inhddt,inhrem,supnam,locnam,stksno,stkdes,inlsno,inlcpr,inlqty,inldes,inhucdnew,inhmrqreqby FROM inltran
//                 left join inhtran on inlinhsno=inhsno
//                 left join supmast on inhsupsno=supsno
//                 left join locmast on inhlocsno=locsno
//                 left join stkmast on inlstksno=stksno

//                 WHERE inhtyp='21' and (inlappyn='' OR inlsno IN ($in_clause))
//                 order by inhsno ASC limit 500";
//  //echo "$sql<br>";
//  $res = mysqli_query($conn, $sql) or die("sql error inl : ".mysqli_error($conn));

//  // Check SQL error
//  if (!$res) {
//      die("<b>SQL ERROR:</b> " . mysqli_error($conn) . "<br>Query: " . $sql);
//  }

//  // No Data
//  if (mysqli_num_rows($res) == 0) {
//      die("<div class='alert alert-warning'>No items found for type: <b>$type</b></div>");
//  }

//  // Table Output
//  echo '
//  <style>
//     .table-container {
//         width: 100%;
//     }
//     .table thead th {
//         position: sticky;
//         top: 0;
//         background: #f8f9fa;
//         z-index: 1;
//         border-bottom: 2px solid #dee2e6;
//         font-weight: 600;
//         color: #333;
//         padding: 12px 8px !important;
//         font-size: 13px;
//     }
//     .table tbody tr {
//         background-color: white;
//         border-bottom: 1px solid #eee;
//     }
//     .table tbody td {
//         padding: 10px 8px !important;
//         font-size: 13px;
//         vertical-align: middle;
//     }
//     .table tbody tr:hover {
//         background-color: #fafafa;
//     }
//  </style>

//  <div class="table-container">
//  <table class="table table-bordered" style="margin-bottom: 0;">
//      <thead>
//          <tr>
//              <th>Location</th>
//              <th>Purchase Order Number</th>
//              <th>Date</th>
//              <th>Item</th>
//              <th>Supplier</th>
//              <th>Price</th>
//              <th>Qty</th>
//              <th>Details</th>
//              <th>Requested By</th>
//              <th>More</th>
//              <th>Approve</th>
//              <th>User</th>
//          </tr>
//      </thead>
//      <tbody>
//  ';
//  while ($row = mysqli_fetch_assoc($res)) {
//      $te_ =$row['inlsno'].'##'.'purchase';
//      $is_saved = in_array($te_, $saved_ids);
//      $checkbox_html = $is_saved ?
//          '<span class="badge bg-success">✓ Approved</span>' :
//          '<input type="checkbox" value="'.$te_.'" class="approveBox">';
//      $row_style = $is_saved ? 'background-color: #d4edda;' : '';
//      echo "
//      <tr style='{$row_style}'>
//          <td>{$row['locnam']}</td>
//          <td>{$row['inhdno']}</td>
//          <td>{$row['inhddt']}</td>
//          <td>{$row['stkdes']}</td>
//          <td>{$row['supnam']}</td>
//          <td>{$row['inlcpr']}</td>
//          <td>{$row['inlqty']}</td>
//          <td>{$row['inhrem']} {$row['inldes']}</td>
//          <td>{$row['inhmrqreqby']}</td>

//          <td>
//              <button onclick='showHistory({$row['stksno']})'
//                      class='btn btn-info btn-sm'>More</button>
//          </td>
//          <td>
//              $checkbox_html
//          </td>
//          <td>{$row['inhucdnew']}</td>
//      </tr>
//      ";
//  }

//  echo "    </tbody>
//  </table>
//  </div>";

// }

if($type=='direct_payment')
{

 // Query modified to include recently approved IDs
 $sql_est = "SELECT inhdno, inhddt, inhestrefno, inhrem, supnam, locnam,
                    inlsno, inhsno, inlqty, inlcpr, inlval, inldes,
                    inhucdnew, inlappyn, inhval, inlgensno, inlval, inlucdnew, inldes, gendes
             FROM inltran
             LEFT JOIN inhtran ON inlinhsno=inhsno
             LEFT JOIN locmast ON inhlocsno=locsno
             LEFT JOIN supmast ON inhsupsno=supsno
             LEFT JOIN g_genmast ON inlgensno=gensno
             WHERE inhtyp=30 AND (inlappyn='' OR inlappyn IS NULL OR inhsno IN ($in_clause))
             GROUP BY inhsno
             ORDER BY inhdno ASC";

 //echo "$sql_est<br>";
 $result_est = mysqli_query($conn, $sql_est) or die("sql error estimate : ".mysqli_error($conn));

 // Check SQL error
 if (!$result_est) {
     die("<b>SQL ERROR:</b> " . mysqli_error($conn) . "<br>Query: " . $sql_est);
 }

 // No Data
 if (mysqli_num_rows($result_est) == 0) {
     die("<div class='alert alert-warning'>No unapproved Documents found.</div>");
 }

 // Table Output
 echo '
 <style>
    .table-container {
        width: 100%;
    }
    .table thead th {
        position: sticky;
        top: 0;
        background: #f8f9fa;
        z-index: 1;
        border-bottom: 2px solid #dee2e6;
        font-weight: 600;
        color: #333;
        padding: 12px 8px !important;
        font-size: 13px;
    }
    .table tbody tr {
        background-color: white;
        border-bottom: 1px solid #eee;
    }
    .table tbody td {
        padding: 10px 8px !important;
        font-size: 13px;
        vertical-align: middle;
    }
    .table tbody tr:hover {
        background-color: #fafafa;
    }
 </style>
 <div class="table-container">
 <table class="table table-bordered" style="margin-bottom: 0;">
     <thead>
         <tr>
             <th>No.</th>
             <th>Pay To</th>
             <th>Total Amount</th>
             <th>Reproduce</th>
             <th>Remarks</th>
             <th>More</th>
             <th>Approve</th>
             <th>User</th>
         </tr>
     </thead>
     <tbody>
 ';
 while ($row = mysqli_fetch_assoc($result_est)) {
    $te_ = $row['inhsno'].'##'.'dirpay';
    $is_saved = in_array($te_, $saved_ids);
    $checkbox_html = $is_saved ?
         '<span class="badge bg-success">✓ Approved</span>' :
         '<input type="checkbox" value="'.$te_.'" class="approveBox">';
    $row_style = $is_saved ? 'background-color: #d4edda;' : '';
     echo "
     <tr style='{$row_style}'>
         <td>{$row['inhdno']}</td>
         <td>{$row['supnam']}</td>
         <td>{$row['inhval']}</td>
         <td>-</td>
         <td>{$row['inhrem']}</td>

         <td>
             <button onclick='showDpLines({$row['inhsno']})'
                     class='btn btn-info btn-sm'>More</button>
         </td>
         <td>
             $checkbox_html
         </td>
         <td>{$row['inhucdnew']}</td>

     </tr>
     ";
 }

 echo "    </tbody>
 </table>
 </div>";

}
        exit;
    }

    if ($action === 'fetch_history') {
if(!isset($_POST['id'])) {
    die("<div class='alert alert-danger'>No item ID provided</div>");
}

if(!isset($_POST['modopt'])) {
    die("<div class='alert alert-danger'>No mode specified</div>");
}

$te_id = intval($_POST['id']);
$modopt = $_POST['modopt'];

if($te_id <= 0) {
    die("<div class='alert alert-danger'>Invalid ID</div>");
}

// Purchase Order History
if($modopt == 'poh') {
    $sql = "SELECT inhddt,supnam,stkdes,inlcpr,inlqty FROM inltran
               left join inhtran on inlinhsno=inhsno
               left join supmast on inhsupsno=supsno
               left join stkmast on inlstksno=stksno
               WHERE inhtyp='21' and inlstksno='$te_id'
               order by inhddt desc limit 5";

    $res = mysqli_query($conn, $sql);

    if(!$res) {
        die("<div class='alert alert-danger'>Error: " . mysqli_error($conn) . "</div>");
    }

    if(mysqli_num_rows($res) == 0) {
        die("<div class='alert alert-info'>No purchase history found for this item</div>");
    }

    echo "<table class='table table-striped'>";
    echo "<tr>";
    echo "<th>Date</th>";
    echo "<th>Supplier</th>";
    echo "<th>Item</th>";
    echo "<th>Qty</th>";
    echo "<th>Price</th>";
    echo "</tr>";

    while($r = mysqli_fetch_assoc($res)){
        echo "<tr>";
        echo "<td>{$r['inhddt']}</td>";
        echo "<td>{$r['supnam']}</td>";
        echo "<td>{$r['stkdes']}</td>";
        echo "<td>{$r['inlqty']}</td>";
        echo "<td>{$r['inlcpr']}</td>";
        echo "</tr>";
    }

    echo "</table>";
    mysqli_free_result($res);
}
elseif($modopt == 'dph') {
    $sql = "SELECT inhddt,inldes, gendes, inlval
            FROM inltran
            left join inhtran on inlinhsno=inhsno
            LEFT JOIN g_genmast ON inlgensno=gensno
            WHERE inhtyp='30' and inlinhsno='$te_id'
            order by inhddt desc limit 5";

    $res = mysqli_query($conn, $sql);

    // print_r($sql);

    if(!$res) {
        die("<div class='alert alert-danger'>Error: " . mysqli_error($conn) . "</div>");
    }

    if(mysqli_num_rows($res) == 0) {
        die("<div class='alert alert-info'>No direct payment history found for this item</div>");
    }

    echo "<table class='table table-striped'>";
    echo "<tr>";
    echo "<th>Date</th>";
    echo "<th>Remarks</th>";
    echo "<th>Account</th>";
    echo "<th>Value</th>";
    echo "</tr>";

    while($r = mysqli_fetch_assoc($res)){
        echo "<tr>";
        echo "<td>{$r['inhddt']}</td>";
        echo "<td>{$r['inldes']}</td>";
        echo "<td>{$r['gendes']}</td>";
        echo "<td>{$r['inlval']}</td>";
        echo "</tr>";
    }

    echo "</table>";
    mysqli_free_result($res);

}
else {
    die("<div class='alert alert-danger'>Unknown mode: " . htmlspecialchars($modopt) . "</div>");
}
        exit;
    }

    if ($action === 'save_approval') {
$te_usdusd=$_SESSION["susdusd"];
//echo "$te_usdusd";

$te_enttime = strftime('%H:%M:%S');
$te_entddt  = strftime ("%Y-%m-%d");
$te_ucdnew  = $te_usdusd;


if($te_usdusd!='ravi' and $te_usdusd!='2000')
{
 header('HTTP/1.1 403 Forbidden');
 exit('You do not have permission to save approvals.');
}

if (!isset($_POST['ids']) || !is_array($_POST['ids'])) {
    echo "No items selected";
    exit;
}

$ids = $_POST['ids'];
$success_count = 0;

// Initialize session array if not exists
if (!isset($_SESSION['saved_approval_ids'])) {
    $_SESSION['saved_approval_ids'] = array();
}

foreach ($ids as $val) {
    // Split the ID and Type (e.g., "123##purchase")
    $parts = explode("##", $val);
    $id = mysqli_real_escape_string($conn, $parts[0]);
    $type = $parts[1];

    $sql = "";
    if ($type == 'purchase' || $type == 'raw' || $type == 'vaccine' || $type == 'chemicals' || $type == 'chickboxes' || $type == 'service' || $type == 'fuel'   ) {

        $sql_inl = "select inlinhsno from inltran where inlsno='$id'";
        $result_inl = mysqli_query($conn,$sql_inl) or die("SQL Error :".mysqli_error($conn));
        $fields_inl    = mysqli_fetch_array($result_inl);
        $te_inlinhsno  = $fields_inl['inlinhsno'];

        $sql_  = "update inhtran set inhappyn='y',inhappucd='$te_ucdnew',inhappddt='$te_entddt',inhapptime='$te_enttime'
                         where inhsno='$te_inlinhsno'";
        $result_    = mysqli_query($conn,$sql_) or die("Could not update inh");

        $sql = "UPDATE inltran SET inlappyn='y',inlappucd='$te_ucdnew',inlappddt='$te_entddt',inlapptime='$te_enttime' WHERE inlsno='$id'";

    }
    elseif ($type == 'chq') {

        $sql = "UPDATE g_grlmast SET grhappyn='y' WHERE grhsno='$id'";

    }
    elseif ($type == 'estm' || $type == 'serv') {

        $sql_  = "update inhtran set inhappyn='y',inhappucd='$te_ucdnew',inhappddt='$te_entddt',inhapptime='$te_enttime'
                         where inhsno='$id'";
        $result_    = mysqli_query($conn,$sql_) or die("Could not update inh");


        $sql = "UPDATE inltran SET inlappyn='y',inlappucd='$te_ucdnew',inlappddt='$te_entddt',inlapptime='$te_enttime' WHERE inlinhsno='$id'";
        //echo "$sql<br>";
    }
    elseif ($type == 'dirpay') {

        $sql_  = "update inhtran set inhappyn='y',inhappucd='$te_ucdnew',inhappddt='$te_entddt',inhapptime='$te_enttime'
                         where inhsno='$id'";
        $result_    = mysqli_query($conn,$sql_) or die("Could not update inh");


        $sql = "UPDATE inltran SET inlappyn='y',inlappucd='$te_ucdnew',inlappddt='$te_entddt',inlapptime='$te_enttime' WHERE inlinhsno='$id'";
    }

    if ($sql != "" && mysqli_query($conn, $sql)) {
        $success_count++;
        // Add to session so it stays visible after reload
        if (!in_array($val, $_SESSION['saved_approval_ids'])) {
            $_SESSION['saved_approval_ids'][] = $val;
        }
    }
}

echo "$success_count items approved successfully.";
        exit;
    }

    header('HTTP/1.1 400 Bad Request');
    exit('Unknown approval action.');
}

require_once dirname(__FILE__) . '/db.php';
$config_use_sessions = TRUE;

// Page and AJAX actions are handled in this file.
$te_usdusd=$_SESSION["susdusd"];

if($te_usdusd!='2000' and $te_usdusd!='ravi')
{
 //return;	
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Order EA Screen</title>
    <link rel="stylesheet" href="css/bootstrap5.min.css">
    <style>
        body {
            background: #eef4ff;
            padding: 20px;
        }

        .radio-box {
            background: #d8e2ff;
            padding: 15px;
            border-radius: 5px;
            text-align: left;
        }

        #itemsTable {
            margin-top: 20px;
            display: none;
        }

        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .search-result-row {
            background-color: #ADD8E6 !important;
        }
    </style>
</head>

<body>

<div class="container">
        <div class="row">
            <div class="col-md-12 ">
                <!-- ▬▬▬▬▬▬▬▬ 16 RADIO BUTTONS ▬▬▬▬▬▬▬▬ -->
                <div class="radio-box">
        <!-- first colomn purple color -->
        <div style="display: inline-block; border-bottom: 1px solid purple; border: 1px solid #cccccc; margin-right: 20px; padding: 5px;">
            <label style="display: block; border: 1px solid #cccccc; margin-bottom: 2px; padding: 2px;"><input type="radio" name="type" value="raw"> Raw Material & Suppliment</label>
            <label style="display: block; border: 1px solid #cccccc; margin-bottom: 2px; padding: 2px;"><input type="radio" name="type" value="vaccine"> Vaccine & Drugs</label>
            <label style="display: block; border: 1px solid #cccccc; margin-bottom: 2px; padding: 2px;"><input type="radio" name="type" value="chemicals"> Disinfected Chemicals</label>
            <label style="display: block; border: 1px solid #cccccc; margin-bottom: 2px; padding: 2px;"><input type="radio" name="type" value="chickboxes"> Chick Boxes</label>
        </div>

        <!-- second colomn purple color -->
        <div style="display: inline-block; border-bottom: 1px solid purple; border: 1px solid #cccccc; margin-right: 20px; padding: 5px;">
            <!--
            <label style="display: block; border: 1px solid #cccccc; margin-bottom: 2px; padding: 2px;"><input type="radio" name="type" value="chq"> Cheque Approval</label>
            -->
            <label style="display: block; border: 1px solid #cccccc; margin-bottom: 2px; padding: 2px;"><input type="radio" name="type" value="purchase"> Purchase Order</label>
            <label style="display: block; border: 1px solid #cccccc; margin-bottom: 2px; padding: 2px;"><input type="radio" name="type" value="service"> Service Orders</label>
            <label style="display: block; border: 1px solid #cccccc; margin-bottom: 2px; padding: 2px;"><input type="radio" name="type" value="fuel"> Fuel Orders</label>
        </div>

        <!-- 3rd colomn blue color -->
        <div style="display: inline-block; border-bottom: 1px solid blue; border: 1px solid #cccccc; margin-right: 20px; padding: 5px;">
            <label style="display: block; border: 1px solid #cccccc; margin-bottom: 2px; padding: 2px;"><input type="radio" name="type" value="supplier_advance"> Supplier Advance</label>
            <label style="display: block; border: 1px solid #cccccc; margin-bottom: 2px; padding: 2px;"><input type="radio" name="type" value="direct_payment"> Direct Payments</label>
            <label style="display: block; border: 1px solid #cccccc; margin-bottom: 2px; padding: 2px;"><input type="radio" name="type" value="estimates"> Estimates</label>
            <label style="display: block; border: 1px solid #cccccc; margin-bottom: 2px; padding: 2px;"><input type="radio" name="type" value="litter_sales"> Litter Sales</label>
        </div>

        <!-- 4th colomn green color -->
        <div style="display: inline-block; border-bottom: 1px solid green; border: 1px solid #cccccc; padding: 5px;">
            <label style="display: block; border: 1px solid #cccccc; margin-bottom: 2px; padding: 2px;"><input type="radio" name="type" value="allow_grn"> Allow Orders for GRN</label>
            <label style="display: block; border: 1px solid #cccccc; margin-bottom: 2px; padding: 2px;"><input type="radio" name="type" value="allow_service_grn"> Allow Orders Service for GRN</label>
            <label style="display: block; border: 1px solid #cccccc; margin-bottom: 2px; padding: 2px;"><input type="radio" name="type" value="allow_price_change"> Allow Orders Price Change</label>
            <label style="display: block; border: 1px solid #cccccc; margin-bottom: 2px; padding: 2px;"><input type="radio" name="type" value="activate_payment_request"> Activate Payment Request</label>
        </div>

    </div>
    </div>
    </div>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px;">
        <input type="text" id="searchBox" placeholder="Search..." class="form-control" style="width: 300px;">
          
        <?php
        if($te_usdusd=='ravi' or $te_usdusd=='2000')
        {
         echo "<button class='btn btn-success' id='saveBtn' style='display:none; white-space: nowrap;'>Save Approval</button> ";
        }
        ?>
        
    </div>

    <!-- ▬▬▬▬▬▬▬▬ TABLE RENDER AREA ▬▬▬▬▬▬▬▬ -->
    <div id="itemsTable"></div>

    <!-- ▬▬▬▬▬▬▬▬ MODAL ▬▬▬▬▬▬▬▬ -->
    <div id="historyModal" class="modal fade" tabindex="-1" aria-labelledby="historyTitle" aria-hidden="true">
        <div class="modal-dialog modal-lg">

            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="historyTitle">Past Purchase History</h4>
                </div>
                <div class="modal-body" id="historyBody">
                    Loading...
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>

        </div>
    </div>

    <script src="jquery/3.2.0/jquery.min.js"></script>
    <script src="js/bootstrap5.bundle.min.js"></script>

    <script>
        var itemsRequest = null;
        var loadVersion = 0;
        function requestError(xhr) {
            if (xhr.status === 401) return 'Your session has expired. Please sign in again.';
            if (xhr.status === 422) return 'This category is not implemented in the available application files.';
            return 'Unable to load data. Please try again. (HTTP ' + xhr.status + ')';
        }
        function loadItems(selected, clearSaved) {
            var version = ++loadVersion;
            if (itemsRequest) itemsRequest.abort();
            $("#saveBtn").hide();
            $("#itemsTable").show().html('<p role="status">Loading...</p>');
            function failed(xhr, status) {
                if (status === 'abort' || version !== loadVersion) return;
                $("#itemsTable").empty().append($('<div class="alert alert-danger" role="alert">').text(requestError(xhr)));
            }
            function fetchItems() {
                if (version !== loadVersion) return;
                itemsRequest = $.post('md_approvals.php', {action: 'fetch_items', type: selected})
                    .done(function(data) {
                        if (version !== loadVersion) return;
                        $("#itemsTable").html(data);
                        $("#saveBtn").toggle($("#itemsTable .approveBox").length > 0);
                    }).fail(failed);
            }
            if (clearSaved) {
                itemsRequest = $.post('md_approvals.php', {action: 'clear_session'}).done(fetchItems).fail(failed);
            } else fetchItems();
        }
        $("input[name=type]").on('change', function () {
            loadItems($(this).val(), true);
        });

        var initialType = $("input[name=type][value=direct_payment]");
        initialType.prop('checked', true);
        loadItems('direct_payment', true);

        function showHistory(item_id) {
            $("#historyBody").html("Loading...");
            bootstrap.Modal.getOrCreateInstance(document.getElementById("historyModal")).show();

            $.post("md_approvals.php", { action: "fetch_history", id: item_id, modopt: 'poh' }, function (data) {
                $("#historyBody").html(data);
            }).fail(function(xhr) {
                $("#historyBody").text(requestError(xhr));
            });
        }

        function showDpLines(item_id) {
            $("#historyBody").html("Loading...");
            bootstrap.Modal.getOrCreateInstance(document.getElementById("historyModal")).show();

            $.post("md_approvals.php", { action: "fetch_history", id: item_id, modopt: 'dph' }, function (data) {
                $("#historyBody").html(data);
            }).fail(function(xhr) {
                $("#historyBody").text(requestError(xhr));
            });
        }

        // Save approval
        $("#saveBtn").click(function () {
            var ticks = [];

            $(".approveBox:checked").each(function () {
                ticks.push($(this).val());
            });
            //alert(ticks);
            
            if (!ticks.length) return;
            var selected = $("input[name=type]:checked").val();
            $("#saveBtn").prop('disabled', true);
            $.post("md_approvals.php", {action: "save_approval", ids: ticks})
                .done(function() {
                    if ($("input[name=type]:checked").val() === selected) loadItems(selected, false);
                }).fail(function(xhr) {
                    alert('Unable to save approvals. Please try again. (HTTP ' + xhr.status + ')');
                }).always(function() { $("#saveBtn").prop('disabled', false); });
        });

        $("#searchBox").on("blur", function () {
            $(this).val("");
        });

        $("#searchBox").on("keyup", function () {
            var searchQuery = $(this).val().toLowerCase();
            
            var $table = $("#itemsTable table");
            
            if (!$table.length) {
                return; 
            }
            
            var $rows = $table.find("tbody tr").not('.separator-row');
            var checkedRows = [];
            var searchResults = [];
            
            $rows.each(function () {
                var $row = $(this);
                var $checkbox = $row.find('.approveBox');
                var isChecked = $checkbox.length > 0 && $checkbox.is(':checked');
                
                var $cells = $row.find("td");
                
                if (searchQuery === "") {
                    $row.removeClass('search-result-row');
                    $row.show();
                    return;
                }
                
                var documentNum0 = $cells.eq(0).text().toLowerCase();
                var documentNum1 = $cells.eq(1).text().toLowerCase();
                var combinedText = documentNum0 + " " + documentNum1;
                var finalDigits = combinedText.replace(/\D/g, '');
                
                var matchFound = finalDigits.endsWith(searchQuery);
                
                if (matchFound) {
                    if (isChecked) {
                        checkedRows.push($row);
                    } else {
                        searchResults.push($row);
                    }
                    $row.show();
                } else {
                    if (isChecked) {
                        checkedRows.push($row);
                        $row.show();
                    } else {
                        $row.hide();
                    }
                }
            });
            
            var $tbody = $table.find("tbody");
            
            $tbody.find('.separator-row').remove();
            
            if (searchQuery !== "" && (checkedRows.length > 0 || searchResults.length > 0)) {
                searchResults.forEach(function($row) {
                    $row.addClass('search-result-row');
                    $row.find('.approveBox').off('change').on('change', function(e) {
                        var checkbox = this;
                        var $currRow = $(checkbox).closest('tr');
                        if (checkbox.checked) {
                            $currRow.addClass('row-highlighted').removeClass('search-result-row');
                        } else {
                            $currRow.removeClass('row-highlighted').addClass('search-result-row');
                        }
                    });
                    $tbody.append($row);
                });
                
                if (checkedRows.length > 0 && searchResults.length > 0) {
                    var $separator = $('<tr class="separator-row"><td colspan="100%" style="background-color: #f0f0f0; font-weight: bold; padding: 2px;">Checked Items</td></tr>');
                    $tbody.append($separator);
                }
                
                checkedRows.forEach(function($row) {
                    $row.removeClass('search-result-row');
                    $tbody.append($row);
                });
            }
        });
    </script>

</body>

</html>
