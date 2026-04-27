let display = document.getElementById("display");

function append(value){
  if(display.innerText === "0") display.innerText = value;
  else display.innerText += value;
}

document.querySelectorAll(".btn").forEach(btn=>{
  btn.addEventListener("click", ()=>{
    let val = btn.innerText;
    if(!isNaN(val) || val === "." || ["+","-","*","/"].includes(val)){
      append(val);
    }
  });
});

function clearDisplay(){
  display.innerText = "0";
}

function backspace(){
  display.innerText = display.innerText.slice(0,-1) || "0";
}

function calculate(){
  try{
    let result = eval(display.innerText);

    saveHistory(display.innerText, result);

    display.innerText = result;

    loadHistory();   // 🔥 important

  }catch{
    display.innerText = "Error";
  }
}

function saveHistory(exp, res){
  fetch("php/save.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded"
    },
    body: "expression=" + encodeURIComponent(exp) + "&result=" + encodeURIComponent(res)
  });
}

function loadHistory(){
  fetch("php/history.php")
    .then(res => res.text())
    .then(data => {
      document.getElementById("historyList").innerHTML = data;
    });
}

// 🔥 LOAD ON START
window.onload = loadHistory;