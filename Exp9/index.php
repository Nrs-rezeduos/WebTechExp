<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Calculator</title>
  <link rel="stylesheet" href="style.css">
</head>

<body>
<div class="container">

<div class="calculator">

  <div class="display" id="display">0</div>
<<div class="buttons">
        <button class="btn red" onclick="clearDisplay()">C</button>
        <button class="btn op">/</button>
        <button class="btn op">*</button>
        <button class="btn op">-</button>

        <button class="btn">7</button>
        <button class="btn">8</button>
        <button class="btn">9</button>
        <button class="btn op">+</button>

        <button class="btn">4</button>
        <button class="btn">5</button>
        <button class="btn">6</button>
        <button class="btn equals" onclick="calculate()">=</button>

        <button class="btn">1</button>
        <button class="btn">2</button>
        <button class="btn">3</button>
        <button class="btn">0</button>
    </div>

</div>


  <div class="calculator">
    <!-- your calculator -->
  </div>

  <div class="history">
    <h2>History</h2>
    <div id="historyList"></div>
  </div>

</div>

<script src="script.js"></script>

</body>
</html>