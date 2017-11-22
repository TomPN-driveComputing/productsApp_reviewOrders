<?php
    //Tom Price-Nicholson, 22/11/2017
    //Review orders page for the ProductsApp
?>

<!DOCTYPE html>
<html>
    <head>
        <title>ACME corporation orders review</title>
        
        <!--Bootstrap-->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
        
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <link rel="stylesheet" type="text/css" href="/javascript/jquery-ui.min.css">
        <script src="/javascript/jquery.min.js"></script>
        <script src="/javascript/jquery-ui.min.js"></script>
        
        <script>
            
            $("document").ready(function(){
                
                $(".dialogBox").dialog({autoOpen:false, modal:true, dialogClass:"no-close", resizable:false});
                
                $("#editForm").dialog({width:400},{buttons:[{text:"Confirm edit",click:function(){$(this).dialog("close");confirmEdit();}},
                        {text:"Close",click:function(){$(this).dialog("close");}}]});
                
                $("#deleteBox").dialog({buttons:[{text:"Yes",click:function(){$(this).dialog("close");confirmDelete();}},
                        {text:"No",click:function(){$(this).dialog("close");}}]});
                
                $.ajax({
                    url:"ajax_calls.php",
                    cache:false,
                    async:true,
                    type:'POST',
                    data:{'request':'getOrders'},
                    dataType:'json',
                    success:function(data)
                    {
                        var orders = data.orderData;
                        for (var i=0;i<orders.length;i++)
                        {
                            var buttonEnable = "disabled";
                            var rowClass = "";
                            
                            if (orders[i].orderStatus === "Cancelled" || orders[i].orderStatus === "Delivered")
                            {
                                buttonEnable = "";
                            }
                            
                            if (orders[i].orderStatus === "Cancelled")
                            {
                                rowClass = "danger";
                            }
                            else if (orders[i].orderStatus === "Dispatched")
                            {
                                rowClass = "info";
                            }
                            else if (orders[i].orderStatus === "Delivered")
                            {
                                rowClass = "success";
                            }
                            else if (orders[i].orderStatus === "Pending review")
                            {
                                rowClass = "warning";
                            }
                            
                            $("#orderTable").append("<tr class="+rowClass+">"
                                    +"<td>"+orders[i].prodName+"</td>"
                                    +"<td>"+orders[i].orderQty+"</td>"
                                    +"<td>"+orders[i].buyerName+"</td>"
                                    +"<td>"+orders[i].buyerAddress+", "+orders[i].buyerPostcode+"</td>"
                                    +"<td>"+orders[i].orderStatus+"</td>"
                                    +"<td><button onclick='editOrder("+orders[i].orderId+")'><span class='glyphicon glyphicon-edit'></span></button></td>"
                                    +"<td><button "+buttonEnable+" onclick='deleteOrder("+orders[i].orderId+")'><span class='glyphicon glyphicon-remove-sign'></span></button></td>"
                                    +"</tr>");
                        }
                    }
                });
            });
            
            function editOrder(orderId)
            {
                $.ajax({
                    url:"ajax_calls.php",
                    cache:false,
                    async:true,
                    type:'POST',
                    data:{'request':'getOrderById','orderId':orderId},
                    dataType:'json',
                    success:function(data)
                    {
                        var orders = data.orderData;
                        var status = orders.orderStatus;
                        
                        if (status === "Pending review")
                        {
                            $("#dispatched").prop("disabled", true);
                            $("#delivered").prop("disabled", true);
                        }
                        else if (status === "Stock allocated")
                        {
                            $("#pending").prop("disabled", true);
                            $("#delivered").prop("disabled", true);
                        }
                        else if (status === "Dispatched")
                        {
                            $("#pending").prop("disabled", true);
                            $("#allocated").prop("disabled", true);
                            $("#cancelled").prop("disabled", true);
                        }
                        else if (status === "Delivered" || status === "Cancelled")
                        {
                            $("#orderStatus").prop("disabled", true);
                        }
                        
                        $("#orderId").val(orders.orderId);
                        $("#productName").html(orders.prodName);
                        $("#productQty").html(orders.orderQty);
                        $("#buyerName").html(orders.buyerName);
                        $("#buyerAddress").html(orders.buyerAddress);
                        $("#buyerPostcode").html(orders.buyerPostcode);
                        
                        $("#editForm").dialog("open");
                    }
                });
            }
            
            function confirmEdit()
            {
                var orderId = $("#orderId").val();
                var orderStatus = $("#orderStatus").val();
                
                $.ajax({
                    url:"ajax_calls.php",
                    cache:false,
                    async:true,
                    type:'POST',
                    data:{'request':'editOrder','orderId':orderId,'orderStatus':orderStatus},
                    dataType:'json',
                    success:function()
                    {
                        location.reload();
                    }
                });
            }
            
            function deleteOrder(orderId)
            {
                $("#deleteBox").dialog("open");
                $("#deleteId").val(orderId);
            }
            
            function confirmDelete()
            {
                var orderId = $("#deleteId").val();
                
                $.ajax({
                    url:"ajax_calls.php",
                    cache:false,
                    async:true,
                    type:'POST',
                    data:{'request':'deleteOrder','orderId':orderId},
                    dataType:'json',
                    success:function()
                    {
                        location.reload();
                    }
                });
            }
            
        </script>
        
        <style>
            #orderTable {margin:2%;}
            
            .narrowColumn {width:5%;}
            
            .no-close .ui-dialog-titlebar-close {display: none;}
        </style>
        
    </head>
    <body>
        <div id="header">
            <?php include_once 'header.php';?>
        </div>
        
        <div id="editForm" class="dialogBox">
            <h3>Order form</h3><br>
            <input type="hidden" id="orderId">
            Product: <span id="productName"></span><br>
            Qty: <span id="productQty"></span><br><br>
            Buyer name: <span id="buyerName"></span><br><br>
            Buyer address: <span id="buyerAddress"></span><br>
            Buyer postcode: <span id="buyerPostcode"></span><br><br>
            Status: 
            <select id="orderStatus">
                <option id="pending" value="Pending review">Pending review</option>
                <option id="allocated" value="Stock allocated">Stock allocated</option>
                <option id="dispatched" value="Dispatched">Dispatched</option>
                <option id="delivered" value="Delivered">Delivered</option>
                <option id="cancelled" value="Cancelled">Cancelled</option>
            </select>
        </div>
        
        <div id="deleteBox" class="dialogBox">
            <h4>Are you sure?</h4>
            <input type="hidden" id="deleteId">
        </div>
        
        <div id="mainBody" class="container">
            <table id="orderTable" class="table table-hover">
                <tr class="active">
                    <th>
                        Product
                    </th>
                    <th>
                        Qty
                    </th>
                    <th>
                        Buyer
                    </th>
                    <th>
                        Delivery address
                    </th>
                    <th>
                        Status
                    </th>
                    <th class="narrowColumn">
                        Update
                    </th>
                    <th class="narrowColumn">
                        Delete
                    </th>
                </tr>
            </table>
        </div>
    </body>
</html>