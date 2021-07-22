<?php
  // Connection
  if (!isset($conn)) // You may this line
  require("includes/conn.php");

  // Configuration
  $table_name = "products";
  $primary_key = "id";
  $hidden_fields = [$primary_key];
  $debug = false;

  // Helpers
  function err($message, $die=true){ // single methods (seperated for further flexibility)
    echo "<BR><pre>$message</pre>";
    if($die) die;
  }
  function clean($string, $restricted_chars=null){ // Validate a string against restricted chars
    return !in_array($string, $restricted_chars==null?[
      '"', "'", '\\', "!"
    ]:$restricted_chars);
  }
  function values_maker($source, $targets){
    $make = "";
    foreach($targets as $target)
    $make .= "'".(array_key_exists($target, $source)?$source[$target]:"")."', ";
    return substr($make, 0, strlen($make)-2);
  }
  function update_maker($source, $targets){
    $make = "";
    foreach($targets as $target) if(array_key_exists($target, $source))
    $make .= "`$target`='".$source[$target]."', ";
    return substr($make, 0, strlen($make)-2);
  }

  // Operations
  if(isset($_POST['operation'])) switch($_POST['operation']){
    case "Add":
      $green=true;
      foreach(($required_fields = [
        "name",
        "price"
      ]) as $field)
      if(!array_key_exists($field, $_POST))
      $green = false;
      if(!$green) err("Invalid Data!");
      elseif(mysqli_query($conn, "INSERT INTO $table_name(`".implode("`, `", $required_fields)."`) VALUES (".values_maker($_POST, $required_fields).");"))
      $success_message = "Record Created!";
      else err("Unable To Add Record".$debug?(": ".mysqli_error($conn)):"!");     
      break;

    case "Update":
      $green=true;
      foreach(($required_fields = [
        "pk",
        "name",
        "price"
      ]) as $field)
      if(!array_key_exists($field, $_POST))
      $green = false;
      if(!$green) err("Invalid Data!");
      elseif(mysqli_query($conn, "UPDATE $table_name SET ".update_maker($_POST, array_diff($required_fields, ["pk"]))." WHERE `$primary_key`='".$_POST['pk']."';"))
      $success_message = "Record Updated!";
      else err("Unable To Update Record".$debug?(": ".mysqli_error($conn)):"!");     
      break;

    case "Delete":
      $green=true;
      foreach(($required_fields = [
        "pk"
      ]) as $field)
      if(!array_key_exists($field, $_POST))
      $green = false;
      if(!$green) err("Invalid Data!");
      elseif(mysqli_query($conn, "DELETE FROM $table_name WHERE `$primary_key`='".$_POST['pk']."';"))
      $success_message = "Record Deleted!";
      else err("Unable To Delete Record".$debug?(": ".mysqli_error($conn)):"!");     
      break;
    default: err("Invalid Operation!", false);
  }

  // Read (At Last as usual)
  $readed_records = [];
  if($qry=mysqli_query($conn, "SELECT * FROM $table_name;"))
    while($record=mysqli_fetch_assoc($qry))
      $readed_records[]=$record;
  else err("Error while executing query" . $debug?(": ".mysqli_error($conn)):"!");
?>

<!DOCTYPE html>

<html lang="en">
  <head>
    <!-- Metas -->
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Title -->
    <title>Title</title>
    <link rel="stylesheet" href="assets/lib/bootstrap/bootstrap.min.css">
  </head>
  <body>
    <div class="jumbotron">
      <h1 class="display-4 text-center">PHP CRUD</h1>
    </div>

    <!-- Success Message -->
    <?php
      if(isset($success_message)){
        ?><div class="alert alert-success"><?=$success_message?></div><?php
      }
    ?>

    <!-- Read -->
    <div class="jumbotron">
      <h1>Records</h1>
      <?php
        if(count($readed_records) == 0){
          ?>
            <p class="lead text-danger">No Records Found!</p>
          <?php
        } else {
          ?>
            <table class="w-75 mx-auto my-5 table table-dark table-striped table-hover text-center">
              <?php
                if(count($readed_records)>0){
                  ?>
                    <thead>
                      <?php
                        foreach(array_keys($readed_records[0]) as $field_name) if(!in_array($field_name, $hidden_fields)){
                          ?>
                            <th><?=ucfirst(strtolower($field_name))?></th>
                          <?php
                        }
                      ?>
                      <th>Actions</th>
                    </thead>
                  <?php
                }
                ?>
                  <tbody>                
                <?php
                foreach($readed_records as $record){
                  ?>
                    <tr>
                      <?php
                        foreach($record as $field_name=>$value) if(!in_array($field_name, $hidden_fields)){
                          ?>
                            <td title="<?=$field_name?>"><?=$value?></td>
                          <?php
                        }
                        if(array_key_exists($primary_key, $record)){
                          ?>
                            <td title="Actions">
                              <!-- Update Trigger & Modal -->
                              <button type="button" class="btn btn-success" data-toggle="modal" data-target="#update_record_<?=$record[$primary_key]?>">
                                Edit
                              </button>
                              <div class="modal fade" id="update_record_<?=$record[$primary_key]?>" tabindex="-1">
                                <div class="modal-dialog">
                                  <form action="" method="POST" class="modal-content">
                                    <div class="modal-header">
                                      <h5 class="modal-title text-dark">Update Record</h5>
                                      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                      </button>
                                    </div>
                                    <div class="modal-body">
                                      <input type="hidden" name="operation" value="Update">
                                      <input type="hidden" name="pk" value="<?=$record[$primary_key]?>">
                                      <div class="row">
                                        <div class="form-group col-md-6">
                                          <label for="uname">Name</label>
                                          <input type="text" name="name" id="uname" class="form-control" placeholder="Product Name" value="<?=$record['name']?>" required>
                                        </div>
                                        <div class="form-group col-md-6">
                                          <label for="uprice">Price</label>
                                          <input type="number" min="0" step="0.01" name="price" id="uprice" class="form-control" placeholder="Product Price" value="<?=$record['price']?>" required>
                                        </div>
                                        <div class="col-12 text-right">
                                        </div>
                                      </div>
                                    </div>
                                    <div class="modal-footer">
                                      <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                      <button type="submit" class="btn btn-success">Update</button>
                                      <button type="reset" class="btn btn-secondary">Undo Changes</button>
                                    </div>
                                  </form>
                                </div>
                              </div>
                              <!-- Delete Form -->
                              <form action="" method="POST" class="d-inline-block">
                                <input type="hidden" name="operation" value="Delete">
                                <input type="hidden" name="pk" value="<?=$record[$primary_key]?>">
                                <button type="submit" class="btn btn-danger">Delete</button>
                              </form>
                            </td>
                          <?php
                        }
                      ?>
                    </tr>
                  <?php
                }
                ?>
                  <tbody>
                <?php
              ?>
            </table>
          <?php
        }
      ?>
    </div>

    <!-- Create Form -->
    <div class="jumbotron">
      <h1>Add New Record</h1>
      <p class="lead">
        Fill up following form and click add button to add new record to database.
      </p>
      <hr class="my-3">
      <form action="" method="POST" class="row my-4 mx-5">
        <input type="hidden" name="operation" value="Add">
        <div class="form-group col-md-6">
          <label for="name">Name</label>
          <input type="text" name="name" id="name" class="form-control" placeholder="Product Name" required>
        </div>
        <div class="form-group col-md-6">
          <label for="price">Price</label>
          <input type="number" step="0.01" min="0" name="price" id="price" class="form-control" placeholder="Product Price" required>
        </div>
        <div class="col-12 text-right">
          <button type="submit" class="btn btn-success">Add</button>
          <button type="reset" class="btn btn-secondary">Clear</button>
        </div>
      </form>
    </div>

    <!-- Scripts -->
    <script src="assets/lib/jquery/jquery-3.6.0.min.js"></script>
    <script src="assets/lib/bootstrap/bootstrap.min.js"></script>
  </body>
</html>

<!-- 
  Dated : 16th May 2021
  Author : Abdullah
 -->