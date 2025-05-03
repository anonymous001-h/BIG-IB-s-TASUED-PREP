<?php
session_start();

$selectedSubjects = "englishLanguage";
$questionsPerSubject = 2; // Limit questions for this example

// Load questions from JSON files
$examData = [];
foreach ($selectedSubjects as $subject) {
  $filepath = "questions/" . $subject . ".json";
  if (file_exists($filepath)) {
    $json = file_get_contents($filepath);
    $examData[$subject] = json_decode($json, true);

    // Limit the number of questions per subject
    if (count($examData[$subject]) > $questionsPerSubject) {
        $examData[$subject] = array_slice($examData[$subject], 0, $questionsPerSubject);
    }
  }
}

// If no questions were loaded, redirect back to the selection page with an error message
if (empty($examData)) {
    header("Location: select-subjects.html?error=No questions found for the selected subjects.");
    exit();
}

// Initialize exam state
if (!isset($_SESSION['current_subject_index'])) {
  $_SESSION['current_subject_index'] = 0;
  $_SESSION['current_question_index'] = 0;
  $_SESSION['user_answers'] = [];
  foreach ($selectedSubjects as $subject) {
    $_SESSION['user_answers'][$subject] = array_fill(0, count($examData[$subject]), null);
  }
}

$currentSubjectIndex = $_SESSION['current_subject_index'];
$currentQuestionIndex = $_SESSION['current_question_index'];
$userAnswers = $_SESSION['user_answers'];

// Handle form submission (if any)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $subject = $selectedSubjects[$currentSubjectIndex];
  $answer = isset($_POST['answer']) ? intval($_POST['answer']) : null;
  $_SESSION['user_answers'][$subject][$currentQuestionIndex] = $answer;

  // Move to the next question or subject
  if ($currentQuestionIndex < count($examData[$subject]) - 1) {
    $_SESSION['current_question_index']++;
  } else if ($currentSubjectIndex < count($selectedSubjects) - 1) {
    $_SESSION['current_subject_index']++;
    $_SESSION['current_question_index'] = 0;
  } else {
    // Exam is finished, redirect to results page
    header("Location: results.php");
    exit();
  }

  header("Location: exam.php");
  exit();
}

$currentSubject = $selectedSubjects[$currentSubjectIndex];
$currentQuestion = $examData[$currentSubject][$currentQuestionIndex];

function calculateOverallQuestionNumber($currentSubjectIndex, $currentQuestionIndex, $questionsPerSubject) {
    return ($currentSubjectIndex * $questionsPerSubject) + $currentQuestionIndex + 1;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam - BIG IB's TASUED PREP</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="exam-page">
    <header class="exam-header">
        <div class="container">
            <div class="logo">
                <h1>BIG IB's TASUED PREP</h1>
            </div>
            <div class="exam-info">
                <div class="exam-subject" id="current-subject">Subject: <?php echo ucfirst($currentSubject); ?></div>
                <div class="exam-progress">
                    <span id="current-question"><?php echo calculateOverallQuestionNumber($currentSubjectIndex, $currentQuestionIndex, $questionsPerSubject); ?></span>/<span id="total-questions"><?php echo array_sum(array_map(function($subject) { return count($subject); }, $examData)); ?></span>
                </div>
            </div>
        </div>
    </header>

    <main>
        <section class="exam-container">
            <div class="container">
                <div class="exam-wrapper">
                    <form method="post" action="exam.php">
                        <div class="question-area">
                            <div class="question-header">
                                <h3 id="question-number">Question <?php echo $currentQuestionIndex + 1; ?></h3>
                                <div class="question-subject" id="question-subject">Subject: <?php echo ucfirst($currentSubject); ?></div>
                            </div>
                            
                            <div class="question-content">
                                <p id="question-text"><?php echo htmlspecialchars($currentQuestion['question']); ?></p>
                            </div>
                            
                            <div class="options-list" id="options-container">
                                <?php foreach ($currentQuestion['options'] as $index => $option): ?>
                                    <div class="option">
                                        <input type="radio" id="option-<?php echo $index; ?>" name="answer" value="<?php echo $index; ?>" <?php if (isset($userAnswers[$currentSubject][$currentQuestionIndex]) && $userAnswers[$currentSubject][$currentQuestionIndex] == $index) echo 'checked'; ?>>
                                        <label for="option-<?php echo $index; ?>"><?php echo htmlspecialchars($option); ?></label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <div class="exam-navigation">
                            <button type="button" id="prev-btn" class="btn btn-outline" <?php if ($currentSubjectIndex == 0 && $currentQuestionIndex == 0) echo 'disabled'; ?>>Previous</button>
                            <button type="submit" id="next-btn" class="btn btn-primary" <?php if ($currentSubjectIndex == count($selectedSubjects) - 1 && $currentQuestionIndex == count($examData[$currentSubject]) - 1) echo 'style="display: none;"'; ?>>Next</button>
                            <button type="submit" id="submit-btn" class="btn btn-success" <?php if ($currentSubjectIndex != count($selectedSubjects) - 1 || $currentQuestionIndex != count($examData[$currentSubject]) - 1) echo 'style="display: none;"'; ?>>Submit Exam</button>
                        </div>
                    </form>
                 </div>
                 
                 <div class="question-navigation">
                     <h3>Question Navigator</h3>
                     <div class="subject-tabs" id="subject-tabs">
                         <?php foreach ($selectedSubjects as $index => $subject): ?>
                             <button class="subject-tab <?php if ($index == $currentSubjectIndex) echo 'active'; ?>" data-subject="<?php echo $subject; ?>" data-index="<?php echo $index; ?>"><?php echo ucfirst($subject); ?></button>
                         <?php endforeach; ?>
                     </div>
                     <div class="question-numbers" id="question-numbers">
                         <?php for ($i = 0; $i < count($examData[$currentSubject]); $i++): ?>
                             <button class="question-number <?php if ($i == $currentQuestionIndex) echo 'current'; if ($userAnswers[$currentSubject][$i] !== null) echo ' answered'; ?>" data-question="<?php echo $i; ?>"><?php echo $i + 1; ?></button>
                         <?php endfor; ?>
                     </div>
                 </div>
             </div>
         </section>
     </main>
 
     <div id="submit-confirmation" class="modal">
         <div class="modal-content">
             <h3>Submit Exam</h3>
             <p>Are you sure you want to submit your exam?</p>
             <div id="unanswered-warning" style="display: none;">
                 <p class="warning"><i class="fas fa-exclamation-triangle"></i> You have <span id="unanswered-count"></span> unanswered questions.</p>
             </div>
             <div class="modal-actions">
                 <button id="cancel-submit" class="btn btn-outline">Continue Exam</button>
                 <button id="confirm-submit" class="btn btn-primary">Submit Exam</button>
             </div>
         </div>
     </div>
 
     <div id="reload-warning" class="modal">
         <div class="modal-content">
             <h3>Warning!</h3>
             <p>Are you sure you want to reload the page? Exam progress won't be saved.</p>
             <div class="modal-actions">
                 <button id="stay-on-page" class="btn btn-primary">Stay on Page</button>
                 <button id="leave-page" class="btn btn-outline">Leave Page</button>
             </div>
         </div>
     </div>
 
     <script>
         // Prevent accidental page reload
         window.addEventListener('beforeunload', function(e) {
             // Show custom modal instead of browser dialog
             document.getElementById('reload-warning').style.display = 'flex';
             
             // Cancel the event
             e.preventDefault();
             // Chrome requires returnValue to be set
             e.returnValue = '';
         });
         
         // Handle reload warning modal
         document.getElementById('stay-on-page').addEventListener('click', function() {
             document.getElementById('reload-warning').style.display = 'none';
         });
         
         document.getElementById('leave-page').addEventListener('click', function() {
             // Remove the event listener to allow navigation
             window.removeEventListener('beforeunload', function(){});
             window.location.href = 'index.html';
         });
 
         document.querySelectorAll('.subject-tab').forEach(tab => {
             tab.addEventListener('click', function() {
                 window.location.href = 'exam.php?subject=' + this.dataset.subject;
             });
         });
 
         document.querySelectorAll('.question-number').forEach(button => {
             button.addEventListener('click', function() {
                 window.location.href = 'exam.php?question=' + this.dataset.question;
             });
         });
 
         document.getElementById('prev-btn').addEventListener('click', function() {
             window.location.href = 'exam.php?action=prev';
         });
 
         document.getElementById('next-btn').addEventListener('click', function() {
             window.location.href = 'exam.php?action=next';
         });
 
         document.getElementById('cancel-submit').addEventListener('click', function() {
             document.getElementById('submit-confirmation').style.display = 'none';
         });
 
         document.getElementById('confirm-submit').addEventListener('click', function() {
             window.location.href = 'results.php';
         });
     </script>
 </body>
 </html>
