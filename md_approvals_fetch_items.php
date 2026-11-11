<?php
require_once dirname(__FILE__) . '/md_approvals_bootstrap.php';



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
?>
