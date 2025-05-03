<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "GET") {
  if (isset($_GET['subjects']) && is_array($_GET['subjects'])) {
    $_SESSION['selected_subjects'] = $_GET['subjects'];
  } else {
    // If no subjects are selected, redirect back to the selection page with an error message
    header("Location: select-subjects.html?error=Please select at least one subject.");
    exit();
  }

  header("Location: exam.php");
  exit();
}
?>
