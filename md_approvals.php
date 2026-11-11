<?php
require_once dirname(__FILE__) . '/db.php';
$config_use_sessions = TRUE;
session_start();
//md_approvals.php --> md_approvals_fetch_items.php -->md_approvals_save_approval.php
$te_usdusd=$_SESSION["susdusd"];
echo "$te_usdusd<br>";
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

    <div class="header-section">
        <h2 style="color: #333; font-weight: bold;">Order EA</h2>
        <button class="btn btn-success btn-sm" onclick="window.location.href='dashboard.php'">MENU</button>
    </div>

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
        $("input[name=type]").click(function () {
            var selected = $(this).val();
            //alert(selected);
            $("#itemsTable").show().html("<b>Loading...</b>");

            // Clear session saved IDs when changing type
            $.post("md_approvals_clear_session.php", {}, function() {
                $.post("md_approvals_fetch_items.php", { type: selected }, function (data) {
                    $("#itemsTable").html(data);
                    $("#saveBtn").show();
                });
            });
        });

        function showHistory(item_id) {
            $("#historyBody").html("Loading...");
            bootstrap.Modal.getOrCreateInstance(document.getElementById("historyModal")).show();

            $.post("md_approvals_fetch_history.php", { id: item_id, modopt: 'poh' }, function (data) {
                $("#historyBody").html(data);
            });
        }

        function showDpLines(item_id) {
            $("#historyBody").html("Loading...");
            bootstrap.Modal.getOrCreateInstance(document.getElementById("historyModal")).show();

            $.post("md_approvals_fetch_history.php", { id: item_id, modopt: 'dph' }, function (data) {
                $("#historyBody").html(data);
            });
        }

        // Save approval
        $("#saveBtn").click(function () {
            var ticks = [];

            $(".approveBox:checked").each(function () {
                ticks.push($(this).val());
            });
            //alert(ticks);
            
            $.post("md_approvals_save_approval.php", { ids: ticks }, function (response) {
                // alert(response);
                
                // Re-fetch the table with the same type (session contains saved IDs)
                var selected = $("input[name=type]:checked").val();
                if (selected) {
                    $("#itemsTable").html("<b>Loading...</b>");
                    $.post("md_approvals_fetch_items.php", { type: selected }, function (data) {
                        $("#itemsTable").html(data);
                    });
                }
            });
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
