<?php ob_start();
// ─────────────────────────────────────────
//  TASKR — todo.php  (MySQL + XAMPP)
// ─────────────────────────────────────────

// !! CHANGE DB_NAME TO YOUR DATABASE NAME IN PHPMYADMIN !!
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');        // default XAMPP = empty
define('DB_NAME', 'tasks');   // <-- your DB name here

// ── API HANDLER ──
if (isset($_GET['api'])) {
    header('Content-Type: application/json');

    try {
        $db = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8',
            DB_USER, DB_PASS
        );
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Auto-create table
        $db->exec("CREATE TABLE IF NOT EXISTS tasks (
            id         INT          AUTO_INCREMENT PRIMARY KEY,
            text       TEXT         NOT NULL,
            priority   VARCHAR(10)  NOT NULL DEFAULT 'medium',
            done       TINYINT(1)            DEFAULT 0,
            created_at TIMESTAMP             DEFAULT CURRENT_TIMESTAMP
        )");

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'DB Error: ' . $e->getMessage()]);
        exit;
    }

    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'GET') {
        $rows = $db->query('SELECT * FROM tasks ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as &$r) $r['done'] = (int)$r['done'];
        echo json_encode($rows);

    } elseif ($method === 'POST') {
        $body = json_decode(file_get_contents('php://input'), true);
        if (empty($body['text'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Text is required']);
            exit;
        }
        $stmt = $db->prepare('INSERT INTO tasks (text, priority) VALUES (?, ?)');
        $stmt->execute([htmlspecialchars(trim($body['text'])), $body['priority'] ?? 'medium']);
        echo json_encode(['id' => (int)$db->lastInsertId(), 'ok' => true]);

    } elseif ($method === 'PUT') {
        $body = json_decode(file_get_contents('php://input'), true);
        $stmt = $db->prepare('UPDATE tasks SET done=? WHERE id=?');
        $stmt->execute([(int)$body['done'], (int)$body['id']]);
        echo json_encode(['ok' => true]);

    } elseif ($method === 'DELETE') {
        $body = json_decode(file_get_contents('php://input'), true);
        $db->prepare('DELETE FROM tasks WHERE id=?')->execute([(int)$body['id']]);
        echo json_encode(['ok' => true]);

    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>TASKR — To-Do List</title>
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet"/>
  <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
    :root{--ink:#0e0e0e;--paper:#f4efe5;--accent:#d63c2f;--muted:#aaa090;--card:#ede8de;--done:#ddd8cc}
    body{background:var(--paper);color:var(--ink);font-family:'DM Sans',sans-serif;min-height:100vh;display:flex;flex-direction:column;align-items:center;padding:48px 20px 100px;position:relative;overflow-x:hidden}
    body::before{content:'';position:fixed;inset:0;background-image:linear-gradient(rgba(160,148,128,.18) 1px,transparent 1px),linear-gradient(90deg,rgba(160,148,128,.18) 1px,transparent 1px);background-size:44px 44px;pointer-events:none;z-index:0}
    .wrapper{position:relative;z-index:1;width:100%;max-width:660px}

    @keyframes slideDown{from{opacity:0;transform:translateY(-20px)}to{opacity:1;transform:translateY(0)}}
    @keyframes fadeUp{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}
    @keyframes taskIn{from{opacity:0;transform:translateX(-12px)}to{opacity:1;transform:translateX(0)}}

    header{display:flex;justify-content:space-between;align-items:flex-end;border-bottom:3px solid var(--ink);padding-bottom:14px;margin-bottom:32px;animation:slideDown .5s ease both}
    .brand-title{font-family:'Bebas Neue',sans-serif;font-size:clamp(58px,13vw,96px);line-height:.88;letter-spacing:3px}
    .brand-title em{color:var(--accent);font-style:normal}
    .brand-sub{font-size:10px;letter-spacing:4px;text-transform:uppercase;color:var(--muted);margin-top:7px}
    .date-block{text-align:right}
    .date-day{font-family:'Bebas Neue',sans-serif;font-size:46px;line-height:1;color:var(--accent)}
    .date-info{font-size:10px;letter-spacing:3px;text-transform:uppercase;color:var(--muted)}

    .progress-section{margin-bottom:28px;animation:fadeUp .5s .1s ease both}
    .progress-labels{display:flex;justify-content:space-between;font-size:10px;letter-spacing:3px;text-transform:uppercase;color:var(--muted);margin-bottom:8px}
    .bar-track{height:7px;background:var(--done);border:2px solid var(--ink);overflow:hidden}
    .bar-fill{height:100%;background:var(--accent);width:0%;transition:width .45s cubic-bezier(.4,0,.2,1)}

    .filters{display:flex;gap:8px;margin-bottom:22px;flex-wrap:wrap;animation:fadeUp .5s .18s ease both}
    .filter-btn{padding:6px 16px;font-family:'DM Sans',sans-serif;font-size:10px;font-weight:500;letter-spacing:2px;text-transform:uppercase;background:transparent;border:2px solid var(--ink);cursor:pointer;transition:background .15s,color .15s;color:var(--ink)}
    .filter-btn:hover,.filter-btn.active{background:var(--ink);color:var(--paper)}

    .add-form{display:flex;margin-bottom:28px;animation:fadeUp .5s .22s ease both}
    .add-input{flex:1;padding:14px 18px;font-family:'DM Sans',sans-serif;font-size:15px;background:var(--card);border:2px solid var(--ink);border-right:none;color:var(--ink);outline:none;transition:background .2s}
    .add-input::placeholder{color:var(--muted)}
    .add-input:focus{background:#fff}
    .priority-select{padding:14px 10px;font-family:'DM Sans',sans-serif;font-size:12px;background:var(--card);border:2px solid var(--ink);border-right:none;color:var(--ink);cursor:pointer;outline:none;appearance:none}
    .add-btn{padding:14px 22px;font-family:'Bebas Neue',sans-serif;font-size:20px;letter-spacing:2px;background:var(--accent);color:#fff;border:2px solid var(--ink);cursor:pointer;transition:background .15s}
    .add-btn:hover{background:#b82e23}
    .add-btn:disabled{background:var(--muted);cursor:not-allowed}

    .section-label{font-size:10px;letter-spacing:4px;text-transform:uppercase;color:var(--muted);margin-bottom:12px;padding-bottom:6px;border-bottom:1px solid var(--done)}
    #task-list{list-style:none;display:flex;flex-direction:column;gap:10px;margin-bottom:20px;min-height:60px}
    .empty-state{text-align:center;padding:40px 0;color:var(--muted);font-size:12px;letter-spacing:3px;text-transform:uppercase}

    .task-item{display:flex;align-items:center;gap:14px;background:var(--card);border:2px solid var(--ink);padding:14px 16px;transition:box-shadow .15s,transform .15s;animation:taskIn .3s ease both;position:relative;overflow:hidden}
    .task-item:hover{box-shadow:3px 3px 0 var(--ink);transform:translate(-1px,-1px)}
    .task-item.done{background:var(--done);opacity:.72}
    .task-item::before{content:'';position:absolute;left:0;top:0;bottom:0;width:4px}
    .task-item[data-priority="high"]::before{background:#d63c2f}
    .task-item[data-priority="medium"]::before{background:#e8a020}
    .task-item[data-priority="low"]::before{background:#4aa85c}

    .task-check{width:22px;height:22px;border:2px solid var(--ink);background:transparent;flex-shrink:0;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:12px;color:#fff;user-select:none;transition:background .15s}
    .task-check:hover{background:var(--muted)}
    .task-item.done .task-check{background:var(--ink)}

    .task-text{flex:1;font-size:15px;position:relative;word-break:break-word;transition:color .2s}
    .task-text::after{content:'';position:absolute;left:0;top:50%;height:1.5px;background:var(--ink);width:0%;transition:width .3s ease}
    .task-item.done .task-text::after{width:100%}
    .task-item.done .task-text{color:var(--muted)}

    .task-badge{font-size:9px;letter-spacing:2px;text-transform:uppercase;padding:3px 8px;border:1.5px solid currentColor;flex-shrink:0}
    .badge-high{color:#d63c2f}.badge-medium{color:#c88010}.badge-low{color:#3a8a4a}

    .task-delete{background:none;border:none;cursor:pointer;color:var(--muted);font-size:18px;padding:2px 4px;flex-shrink:0;transition:color .15s}
    .task-delete:hover{color:var(--accent)}

    .actions{display:flex;justify-content:flex-end;margin-bottom:20px}
    .clear-btn{padding:8px 18px;font-family:'DM Sans',sans-serif;font-size:10px;letter-spacing:2px;text-transform:uppercase;background:transparent;border:2px solid var(--muted);color:var(--muted);cursor:pointer;transition:border-color .15s,color .15s}
    .clear-btn:hover{border-color:var(--accent);color:var(--accent)}

    .stats{display:flex;border:2px solid var(--ink);animation:fadeUp .5s .3s ease both}
    .stat-box{flex:1;padding:16px;text-align:center;border-right:2px solid var(--ink)}
    .stat-box:last-child{border-right:none}
    .stat-num{font-family:'Bebas Neue',sans-serif;font-size:40px;line-height:1;color:var(--accent)}
    .stat-label{font-size:9px;letter-spacing:3px;text-transform:uppercase;color:var(--muted);margin-top:3px}

    #toast{position:fixed;bottom:28px;left:50%;transform:translateX(-50%) translateY(60px);background:var(--ink);color:var(--paper);padding:10px 24px;font-size:12px;letter-spacing:2px;text-transform:uppercase;border:2px solid var(--accent);opacity:0;transition:all .3s ease;z-index:999;pointer-events:none}
    #toast.show{opacity:1;transform:translateX(-50%) translateY(0)}
    #toast.error{border-color:#ff4444}
  </style>
</head>
<body>
<div class="wrapper">
  <header>
    <div>
      <div class="brand-title">TASK<em>R</em></div>
      <div class="brand-sub">Your daily task manager</div>
    </div>
    <div class="date-block">
      <div class="date-day" id="date-day">—</div>
      <div class="date-info" id="date-info">—</div>
    </div>
  </header>

  <div class="progress-section">
    <div class="progress-labels">
      <span>Today's Progress</span>
      <span id="progress-text">0 of 0 done</span>
    </div>
    <div class="bar-track"><div class="bar-fill" id="bar-fill"></div></div>
  </div>

  <div class="filters">
    <button class="filter-btn active" onclick="setFilter('all',this)">All</button>
    <button class="filter-btn" onclick="setFilter('active',this)">Active</button>
    <button class="filter-btn" onclick="setFilter('done',this)">Completed</button>
    <button class="filter-btn" onclick="setFilter('high',this)">🔴 High</button>
    <button class="filter-btn" onclick="setFilter('medium',this)">🟡 Medium</button>
    <button class="filter-btn" onclick="setFilter('low',this)">🟢 Low</button>
  </div>

  <div class="add-form">
    <input class="add-input" id="task-input" type="text"
      placeholder="Add a new task and press Enter…" maxlength="120"
      onkeydown="if(event.key==='Enter') addTask()"/>
    <select class="priority-select" id="priority-select">
      <option value="medium">⬤ Med</option>
      <option value="high">⬤ High</option>
      <option value="low">⬤ Low</option>
    </select>
    <button class="add-btn" id="add-btn" onclick="addTask()">ADD</button>
  </div>

  <div class="section-label" id="section-label">All Tasks</div>
  <ul id="task-list"></ul>

  <div class="actions">
    <button class="clear-btn" onclick="clearDone()">Clear Completed</button>
  </div>

  <div class="stats">
    <div class="stat-box"><div class="stat-num" id="stat-total">0</div><div class="stat-label">Total</div></div>
    <div class="stat-box"><div class="stat-num" id="stat-active">0</div><div class="stat-label">Active</div></div>
    <div class="stat-box"><div class="stat-num" id="stat-done">0</div><div class="stat-label">Done</div></div>
  </div>
</div>

<div id="toast"></div>

<script>
  const API = 'index.php?api=1';
  let tasks = [], currentFilter = 'all';

  // DATE
  (function(){
    const n = new Date();
    document.getElementById('date-day').textContent =
      n.toLocaleDateString('en-US',{weekday:'short'}).toUpperCase()+' '+n.getDate();
    document.getElementById('date-info').textContent =
      n.toLocaleDateString('en-US',{month:'long',year:'numeric'}).toUpperCase();
  })();

  // TOAST
  function toast(msg, isError=false){
    const el=document.getElementById('toast');
    el.textContent=msg;
    el.className='show'+(isError?' error':'');
    clearTimeout(el._t);
    el._t=setTimeout(()=>el.className='',2500);
  }

  // LOAD
  async function loadTasks(){
    try{
      const res=await fetch(API);
      const data=await res.json();
      if(data.error) throw new Error(data.error);
      tasks=data; render();
    }catch(e){ toast('❌ '+e.message,true); }
  }

  // ADD
  async function addTask(){
    const input=document.getElementById('task-input');
    const btn=document.getElementById('add-btn');
    const text=input.value.trim();
    if(!text){ input.focus(); toast('Please enter a task!',true); return; }
    const priority=document.getElementById('priority-select').value;
    btn.disabled=true; btn.textContent='...';
    try{
      const res=await fetch(API,{
        method:'POST',
        headers:{'Content-Type':'application/json'},
        ob_clean();
        body:JSON.stringify({text,priority})
      });
      const data=await res.json();
      if(data.error) throw new Error(data.error);
      tasks.unshift({id:data.id,text,priority,done:0});
      input.value=''; input.focus();
      toast('✓ Task added!'); render();
    }catch(e){ toast('❌ '+e.message,true); }
    finally{ btn.disabled=false; btn.textContent='ADD'; }
  }

  // TOGGLE
  async function toggleTask(id){
    const t=tasks.find(t=>t.id===id); if(!t) return;
    t.done=t.done?0:1; render();
    await fetch(API,{method:'PUT',headers:{'Content-Type':'application/json'},body:JSON.stringify({id,done:t.done})});
  }

  // DELETE
  async function deleteTask(id){
    tasks=tasks.filter(t=>t.id!==id); render();
    await fetch(API,{method:'DELETE',headers:{'Content-Type':'application/json'},body:JSON.stringify({id})});
    toast('Task removed');
  }

  // CLEAR DONE
  async function clearDone(){
    const done=tasks.filter(t=>t.done);
    if(!done.length){toast('No completed tasks',true);return;}
    tasks=tasks.filter(t=>!t.done); render();
    for(const t of done)
      await fetch(API,{method:'DELETE',headers:{'Content-Type':'application/json'},body:JSON.stringify({id:t.id})});
    toast('Completed tasks cleared');
  }

  // FILTER
  function setFilter(f,btn){
    currentFilter=f;
    document.querySelectorAll('.filter-btn').forEach(b=>b.classList.remove('active'));
    btn.classList.add('active');
    const labels={all:'All Tasks',active:'Active Tasks',done:'Completed Tasks',high:'High Priority',medium:'Medium Priority',low:'Low Priority'};
    document.getElementById('section-label').textContent=labels[f];
    render();
  }

  function getVisible(){
    if(currentFilter==='all')    return tasks;
    if(currentFilter==='active') return tasks.filter(t=>!t.done);
    if(currentFilter==='done')   return tasks.filter(t=> t.done);
    return tasks.filter(t=>t.priority===currentFilter);
  }

  // RENDER
  function render(){
    const list=document.getElementById('task-list');
    const visible=getVisible();
    list.innerHTML=visible.length===0
      ?`<li class="empty-state">No tasks here — add one above</li>`
      :visible.map((t,i)=>`
        <li class="task-item${t.done?' done':''}" data-priority="${t.priority}" style="animation-delay:${i*.04}s">
          <div class="task-check" onclick="toggleTask(${t.id})">${t.done?'✓':''}</div>
          <span class="task-text">${esc(t.text)}</span>
          <span class="task-badge badge-${t.priority}">${t.priority}</span>
          <button class="task-delete" onclick="deleteTask(${t.id})" title="Remove">✕</button>
        </li>`).join('');

    const total=tasks.length, done=tasks.filter(t=>t.done).length, active=total-done;
    document.getElementById('stat-total').textContent=total;
    document.getElementById('stat-active').textContent=active;
    document.getElementById('stat-done').textContent=done;
    document.getElementById('progress-text').textContent=`${done} of ${total} done`;
    document.getElementById('bar-fill').style.width=total?(done/total*100)+'%':'0%';
  }

  function esc(s){return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');}

  loadTasks();
</script>
</body>
</html>
