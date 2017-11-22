<?php
    //Tom Price-Nicholson, 6/11/2017
    //Ajax calls for Products App

    $request = $_POST['request'];
    $orderId = $_POST['orderId'];
    
    $jsonval = new stdClass();
    
    require_once "sql_class.php";
    $sqlClass = new SQLClass;
    
    if ($request == 'getOrders')
    {
        $query = "SELECT Orders.orderId, Orders.orderQty, Orders.buyerName, Orders.buyerAddress, Orders.buyerPostcode, Orders.orderStatus, 
             Products.prodName FROM Orders INNER JOIN Products ON Orders.orderProductId = Products.prodId";
        $data = $sqlClass -> SQL_Read($query);
        $jsonval -> orderData = $data;
    }
    
    else if ($request == 'getOrderById')
    {
        $query = "SELECT * FROM Orders WHERE orderId = '$orderId'";
        $data = $sqlClass -> SQL_Read($query);
        
        $jsonval -> orderData = $data[0];
    }
    
    else if ($request == 'editOrder')
    {
        $orderStatus = $_POST['orderStatus'];
        
        $query1 = "SELECT orderProductId, orderQty, orderStatus FROM Orders WHERE orderId = '$orderId'";
        $data1 = $sqlClass -> SQL_Read($query1);
        
        $query2 = "UPDATE Orders SET orderStatus = '$orderStatus' WHERE orderId = '$orderId'";
        $data2 = $sqlClass -> SQL_Update($query2);
        
        $prodId = $data1[0]['orderProductId'];
        $qty = $data1[0]['orderQty'];
        
        if ($orderStatus === 'Stock allocated')
        {
            $query3 = "SELECT pending, allocated FROM Products WHERE prodId = '$prodId'";
            $data3 = $sqlClass -> SQL_Read($query3);
            
            $pending = $data3[0]['pending'];
            $allocated = $data3[0]['allocated'];
            
            $pending -= $qty;
            $allocated += $qty;
            
            $query4 = "UPDATE Products SET pending = '$pending', allocated = '$allocated' WHERE prodId = '$prodId'";
            $data4 = $sqlClass -> SQL_Update($query4);
        }
        else if ($orderStatus === 'Dispatched')
        {
            $query3 = "SELECT stock, dispatched, allocated FROM Products WHERE prodId = '$prodId'";
            $data3 = $sqlClass -> SQL_Read($query3);
            
            $stock = $data3[0]['stock'];
            $dispatched = $data3[0]['dispatched'];
            $allocated = $data3[0]['allocated'];
            
            $stock -= $qty;
            $dispatched += $qty;
            $allocated -= $qty;
            
            $query4 = "UPDATE Products SET stock = '$stock', dispatched = '$dispatched', allocated = '$allocated' WHERE prodId = '$prodId'";
            $data4 = $sqlClass -> SQL_Update($query4);
        }
        else if ($orderStatus === 'Delivered')
        {
            $query3 = "SELECT dispatched FROM Products WHERE prodId = '$prodId'";
            $data3 = $sqlClass -> SQL_Read($query3);
            
            $dispatched = $data3[0]['dispatched'];
            
            $dispatched -= $qty;
            
            $query4 = "UPDATE Products SET dispatched = '$dispatched' WHERE prodId = '$prodId'";
            $data4 = $sqlClass -> SQL_Update($query4);
        }
        else if ($orderStatus === 'Cancelled')
        {
            $oldOrderStatus = $data1[0]['orderStatus'];
            
            if ($oldOrderStatus === 'Pending review')
            {
                $query3 = "SELECT available, pending FROM Products WHERE prodId = '$prodId'";
                $data3 = $sqlClass -> SQL_Read($query3);
                
                $available = $data3[0]['available'];
                $pending = $data3[0]['pending'];
                
                $available += $qty;
                $pending -= $qty;
                
                $query4 = "UPDATE Products SET available = '$available', pending = '$pending' WHERE prodId = '$prodId'";
                $data4 = $sqlClass -> SQL_Update($query4);
            }
            else if ($oldOrderStatus === 'Stock allocated')
            {
                $query3 = "SELECT available, allocated FROM Products WHERE prodId = '$prodId'";
                $data3 = $sqlClass -> SQL_Read($query3);
                
                $available = $data3[0]['available'];
                $allocated = $data3[0]['allocated'];
                
                $available += $qty;
                $allocated -= $qty;
                
                $query4 = "UPDATE Products SET available = '$available', allocated = '$allocated' WHERE prodId = '$prodId'";
                $data4 = $sqlClass -> SQL_Update($query4);
            }
        }
    }
    
    else if ($request == 'deleteOrder')
    {
        $orderStatus = $_POST['orderStatus'];
        
        $query = "DELETE FROM Orders WHERE orderId = '$orderId'";
        $data = $sqlClass -> SQL_Delete($query);
    } 
    
    echo json_encode($jsonval);
?>