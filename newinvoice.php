<?php

    include_once("connect/db_cls_connect.php");
    include_once("main.class.php");

    $db = new dbObj();
    $connString =  $db->getConnstring();
    $mainCls = new Main($connString);
    $invoice_data_arr = $mainCls->getAllInvoceList();

    print_r($invoice_data_arr);

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <title></title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/jquery.dataTables.min.js"></script>
    <script src="js/dataTables.bootstrap.min.js"></script>
    <link rel="stylesheet" href="css/dataTables.bootstrap.min.css">
    <style>
      /* Remove the navbar's default margin-bottom and rounded borders */ 
      .navbar {
      margin-bottom: 4px;
      border-radius: 0;
      }
      /* Add a gray background color and some padding to the footer */
      footer {
      background-color: #f2f2f2;
      padding: 25px;
      }
      .carousel-inner img {
      width: 100%; /* Set width to 100% */
      margin: auto;
      min-height:200px;
      }
      .navbar-brand
      {
      padding:5px 40px;
      }
      .navbar-brand:hover
      {
      background-color:#ffffff;
      }
      /* Hide the carousel text when the screen is less than 600 pixels wide */
      @media (max-width: 600px) {
      .carousel-caption {
      display: none; 
      }
      }
      <style>
      .box
        {
        width: 100%;
        max-width: 1390px;
        border-radius: 5px;
        border:1px solid #ccc;
        padding: 15px;
        margin: 0 auto;                
        margin-top:50px;
        box-sizing:border-box;
        }
      </style>
    <link rel="stylesheet" href="css/datepicker.css">
    <script src="js/bootstrap-datepicker1.js"></script>
    <script>
      $(document).ready(function(){
        $('#order_date').datepicker({
          format: "yyyy-mm-dd",
          autoclose: true
        });
      });
    </script>
  </head>
  <body>
      <div class="container-fluid">
        <h3 align="center">Invoce List</h3>
    <div align="right">
        <a href="invoice.php?add=1" class="btn btn-info btn-xs">Create</a>
      </div>
      <br />
      <table id="data-table" class="table table-bordered table-striped">
        <thead>
          <tr>
            <th>Invoice No.</th>
            <th>Invoice Date</th>
            <th>Receiver Name</th>
            <th>Invoice Total</th>
            <th>PDF</th>
            <th>Edit</th>
            <th>Delete</th>
          </tr>
        </thead>
        <?php
        if($total_rows > 0)
        {
          foreach($all_result as $row)
          {
            echo '
              <tr>
                <td>'.$row["order_no"].'</td>
                <td>'.$row["order_date"].'</td>
                <td>'.$row["order_receiver_name"].'</td>
                <td>'.$row["order_total_after_tax"].'</td>
                <td><a href="print_invoice.php?pdf=1&id='.$row["order_id"].'">PDF</a></td>
                <td><a href="invoice.php?update=1&id='.$row["order_id"].'"><span class="glyphicon glyphicon-edit"></span></a></td>
                <td><a href="#" id="'.$row["order_id"].'" class="delete"><span class="glyphicon glyphicon-remove"></span></a></td>
              </tr>
            ';
          }
        }
        ?>
      </table>
    </div>
    </body>
</html>