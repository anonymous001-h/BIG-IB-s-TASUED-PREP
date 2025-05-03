<?php
session_start();

// Check if subjects are selected
if (!isset($_SESSION['selected_subjects']) || !is_array($_SESSION['selected_subjects'])) {
    header("Location: select-subjects.html?error=No subjects selected.");
    exit();
}

$selectedSubjects = $_SESSION['selected_subjects'];
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

// Get user answers from session
$userAnswers = $_SESSION['user_answers'];

// Calculate results
$totalQuestions = 0;
$answeredQuestions = 0;
$skippedQuestions = 0;
$correctAnswers = 0;
$incorrectAnswers = 0;

$subjectResults = [];

foreach ($selectedSubjects as $subject) {
    $answers = $userAnswers[$subject];
    $questions = $examData[$subject];

    if (!is_array($answers) || !is_array($questions)) continue;

    $subjectCorrect = 0;
    $subjectIncorrect = 0;
    $subjectSkipped = 0;

    foreach ($answers as $index => $answer) {
        if ($index >= count($questions)) continue;

        $totalQuestions++;

        if ($answer === null) {
            $skippedQuestions++;
            $subjectSkipped++;
        } else {
            $answeredQuestions++;

            if ($answer == $questions[$index]['correctAnswer']) {
                $correctAnswers++;
                $subjectCorrect++;
            } else {
                $incorrectAnswers++;
                $subjectIncorrect++;
            }
        }
    }

    $subjectResults[$subject] = [
        'total' => count($answers),
        'correct' => $subjectCorrect,
        'incorrect' => $subjectIncorrect,
        'skipped' => $subjectSkipped,
        'score' => count($answers) > 0 ? round(($subjectCorrect / count($answers)) * 100) : 0
    ];
}

// Calculate and update score percentage
$scorePercentage = $totalQuestions > 0 ? round(($correctAnswers / $totalQuestions) * 100) : 0;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Results - BIG IB's TASUED PREP</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <header>
        <div class="container">
            <div class="logo">
                <h1>BIG IB's TASUED PREP</h1>
                <p>Your Ultimate TASUED Post-UTME Preparation Platform</p>
            </div>
            <nav>
                <ul>
                    <li><a href="index.html">Home</a></li>
                    <li><a href="about.html">About Me</a></li>
                    <li><a href="community.html">Community</a></li>
                </ul>
            </nav>
            <div class="menu-toggle">
                <i class="fas fa-bars"></i>
            </div>
        </div>
    </header>

    <main>
        <section class="results-summary">
            <div class="container">
                <h2>Your Exam Results</h2>
                
                <div class="results-card">
                    <div class="results-header">
                        <h3>Performance Summary</h3>
                    </div>
                    
                    <div class="results-overview">
                        <div class="result-stat">
                            <div class="stat-value"><?php echo $totalQuestions; ?></div>
                            <div class="stat-label">Total Questions</div>
                        </div>
                        <div class="result-stat">
                            <div class="stat-value"><?php echo $answeredQuestions; ?></div>
                            <div class="stat-label">Answered</div>
                        </div>
                        <div class="result-stat">
                            <div class="stat-value"><?php echo $skippedQuestions; ?></div>
                            <div class="stat-label">Skipped</div>
                        </div>
                        <div class="result-stat correct">
                            <div class="stat-value"><?php echo $correctAnswers; ?></div>
                            <div class="stat-label">Correct</div>
                        </div>
                        <div class="result-stat incorrect">
                            <div class="stat-value"><?php echo $incorrectAnswers; ?></div>
                            <div class="stat-label">Incorrect</div>
                        </div>
                    </div>
                    
                    <div class="score-container">
                        <div class="score-circle">
                            <div class="score-value"><?php echo $scorePercentage; ?>%</div>
                        </div>
                        <div class="score-label">Your Score</div>
                    </div>
                    
                    <div class="subject-breakdown" id="subject-breakdown">
                        <h3>Subject Breakdown</h3>
                        <div class="subject-scores" id="subject-scores">
                            <?php foreach ($subjectResults as $subject => $result): ?>
                                <div class="subject-score">
                                    <div class="subject-name"><?php echo ucfirst($subject); ?></div>
                                    <div class="score-bar">
                                        <div class="score-progress" style="width: <?php echo $result['score']; ?>%;"></div>
                                    </div>
                                    <div class="score-value"><?php echo $result['score']; ?>%</div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        
        <section class="detailed-results">
            <div class="container">
                <h2>Detailed Analysis</h2>
                
                <div class="results-tabs" id="results-tabs">
                    <?php foreach ($selectedSubjects as $index => $subject): ?>
                        <button class="results-tab <?php if ($index == 0) echo 'active'; ?>" data-subject="<?php echo $subject; ?>"><?php echo ucfirst($subject); ?></button>
                    <?php endforeach; ?>
                </div>
                
                <div class="questions-review" id="questions-review">
                    <?php foreach ($selectedSubjects as $subject): ?>
                        <?php foreach ($examData[$subject] as $index => $question): ?>
                            <div class="question-review <?php
                                if ($userAnswers[$subject][$index] === null) {
                                    echo 'skipped';
                                } elseif ($userAnswers[$subject][$index] == $question['correctAnswer']) {
                                    echo 'correct';
                                } else {
                                    echo 'incorrect';
                                }
                            ?>">
                                <div class="question-header">
                                    <h4>Question <?php echo $index + 1; ?></h4>
                                    <div class="question-status">
                                        <?php if ($userAnswers[$subject][$index] === null): ?>
                                            <i class="fas fa-minus-circle"></i> Skipped
                                        <?php elseif ($userAnswers[$subject][$index] == $question['correctAnswer']): ?>
                                            <i class="fas fa-check-circle"></i> Correct
                                        <?php else: ?>
                                            <i class="fas fa-times-circle"></i> Incorrect
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="question-content"><?php echo htmlspecialchars($question['question']); ?></div>
                                <div class="options-review">
                                    <?php foreach ($question['options'] as $optionIndex => $option): ?>
                                        <div class="option-review <?php
                                            if ($optionIndex == $question['correctAnswer']) echo 'correct-answer';
                                            if ($userAnswers[$subject][$index] == $optionIndex && $optionIndex != $question['correctAnswer']) echo 'user-wrong';
                                        ?>">
                                            <div class="option-text"><?php echo htmlspecialchars($option); ?></div>
                                            <div class="option-icon">
                                                <?php if ($optionIndex == $question['correctAnswer']): ?>
                                                    <i class="fas fa-check"></i>
                                                <?php elseif ($userAnswers[$subject][$index] == $optionIndex): ?>
                                                    <i class="fas fa-times"></i>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="explanation">
                                    <h5>Explanation:</h5>
                                    <p><?php echo htmlspecialchars($question['explanation']); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        
        <section class="results-actions">
            <div class="container">
                <div class="action-buttons">
                    <a href="select-subjects.html" class="btn btn-primary">Take Another Test</a>
                    <a href="index.html" class="btn btn-outline">Back to Home</a>
                </div>
            </div>
        </section>
        
        <section class="community-cta">
            <div class="container">
                <div class="cta-content">
                    <h3>Join Our Community!</h3>
                    <p>Connect with fellow TASUED aspirants, get study tips, and access exclusive resources.</p>
                    <a href="community.html" class="btn btn-secondary">Join Now</a>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-logo">
                    <h3>BIG IB's TASUED PREP</h3>
                    <p>Helping TASUED aspirants achieve success</p>
                </div>
                <div class="footer-links">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="index.html">Home</a></li>
                        <li><a href="about.html">About Me</a></li>
                        <li><a href="community.html">Community</a></li>
                        <li><a href="select-subjects.html">Start Practice</a></li>
                    </ul>
                </div>
                <div class="footer-contact">
                    <h4>Contact</h4>
                    <p>Mudasiru Ibrahim Opemipo (BIG IB)</p>
                    <p>200 Level Student</p>
                    <p>Tai Solarin University of Education</p>
                    <p>09165360312</p>
                    <p>ibrahimmudasiru07@gmail.com</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 BIG IB's TASUED PREP. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        document.querySelectorAll('.results-tab').forEach(tab => {
            tab.addEventListener('click', function() {
                // Add your code here to show the questions for the selected subject
                console.log('Clicked on subject tab: ' + this.dataset.subject);
            });
        });
    </script>
</body>
</html>
<?php
session_destroy();
?>
