<?php
function generateRandomColor() {
    // Generate a random hex color
    $randomColor = '#' . dechex(rand(0x000000, 0xFFFFFF));
    return $randomColor;
}
function getFirstLetters($input) {
    // Split the string into words
    $words = explode(' ', $input);
    // Initialize an empty string for the result
    $firstLetters = '';
    // Loop through the words and get the first letter of each
    foreach ($words as $word) {
        if (!empty($word)) {
            $firstLetters .= strtoupper($word[0]);  // Add the first letter of each word
        }
    }
    return $firstLetters;
}
$bd=generateRandomColor();
// Example usage:
$name="Muhammad Khan";
// echo getFirstLetters($name);  // Output: "HW"
echo '<div style="
padding:5px;
background:'.$bd.';  
font-family: system-ui;
display:flex;
align-items:center;
justify-content:center;
border-radius:50%; 
color:white;
width:40px; 
height:40px;">
<h2>
'
.
getFirstLetters($name)
.
'
</h2>
</div>';
