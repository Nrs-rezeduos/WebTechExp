const students = [
    {name: 'Namon', age: 20, department: 'Computer Science', marks: 100},
    {name: 'Ishan', age: 22, department: 'Chemistry', marks: 75},
    {name: 'David', age: 19, department: 'Mathematics', marks: 88},
    {name: 'Bhupe', age: 21, department: 'Biology', marks: 10},
    {name: 'Kesav', age: 23, department: 'Computer Science', marks: 92},
    {name: 'Pratyush', age: 20, department: 'History', marks: 55},
    {name: 'Rachit', age: 24, department: 'English', marks: 81},
    {name: 'Abinav', age: 22, department: 'Art', marks: 68},
    {name: 'Dikshit', age: 21, department: 'Economics', marks: 73},
    {name: 'Devraj', age: 20, department: 'Philosophy', marks: 68}
];

const tableBody = document.getElementById('student-body');

// filter controls
const nameInput = document.getElementById('searchName');
const deptSelect = document.getElementById('filterDept');

function renderStudents(list) {
    // clear any previous results
    document.getElementById('highest').textContent = '';
    document.getElementById('passfail').textContent = '';
    tableBody.innerHTML = '';
    list.forEach(s => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${s.name}</td>
            <td>${s.age}</td>
            <td>${s.department}</td>
            <td>${s.marks}</td>
        `;
        tableBody.appendChild(tr);
    });
}

// filter helpers
function populateDepartments() {
    const depts = [...new Set(students.map(s => s.department))];
    depts.sort();
    depts.forEach(d => {
        const opt = document.createElement('option');
        opt.value = d;
        opt.textContent = d;
        deptSelect.appendChild(opt);
    });
}

function updateFilters() {
    const term = nameInput.value.trim().toLowerCase();
    const dept = deptSelect.value;
    const filtered = students.filter(s => {
        const matchName = s.name.toLowerCase().includes(term);
        const matchDept = dept === '' || s.department === dept;
        return matchName && matchDept;
    });
    renderStudents(filtered);
}

function showHighest() {
    const max = students.reduce((prev, cur) => cur.marks > prev.marks ? cur : prev, students[0]);
    const box = document.getElementById('highest');
    box.textContent = `Highest marks: ${max.name} - ${max.marks}`;
}

function sortByMarks() {
    const sorted = [...students].sort((a, b) => b.marks - a.marks);
    renderStudents(sorted);
}

function showPassFail() {
    const pass = students.filter(s => s.marks >= 50);
    const fail = students.filter(s => s.marks < 50);
    const box = document.getElementById('passfail');
    let html = '<strong>Passed:</strong> ' + pass.map(s => s.name + ' (' + s.marks + ')').join(', ') + '<br>';
    html += '<strong>Failed:</strong> ' + fail.map(s => s.name + ' (' + s.marks + ')').join(', ');
    box.innerHTML = html;
}

function toggleCase(upper=true) {
    students.forEach(s => {
        s.name = upper ? s.name.toUpperCase() : s.name.toLowerCase();
    });
    renderStudents(students);
}

// setup filters
populateDepartments();
nameInput.addEventListener('input', updateFilters);
deptSelect.addEventListener('change', updateFilters);

// initial render (with filters applied)
updateFilters();
