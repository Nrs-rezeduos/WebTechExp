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

// products dataset
const products = [
    {name: 'Smartphone', category: 'Electronics', price: 699, amount: 50, rating: 4.5},
    {name: 'Laptop', category: 'Electronics', price: 1099, amount: 30, rating: 4.7},
    {name: 'Headphones', category: 'Electronics', price: 199, amount: 80, rating: 4.3},
    {name: 'Coffee Maker', category: 'Home', price: 89, amount: 40, rating: 4.0},
    {name: 'Electric Kettle', category: 'Home', price: 45, amount: 60, rating: 4.2},
    {name: 'Office Chair', category: 'Furniture', price: 150, amount: 20, rating: 3.9},
    {name: 'Desk Lamp', category: 'Furniture', price: 25, amount: 100, rating: 4.6},
    {name: 'Monitor', category: 'Electronics', price: 249, amount: 25, rating: 4.4},
    {name: 'Keyboard', category: 'Electronics', price: 49, amount: 90, rating: 4.1},
    {name: 'Mouse', category: 'Electronics', price: 29, amount: 120, rating: 4.0}
];

const tableBody = document.getElementById('student-body');
const productBody = document.getElementById('product-body');

// filter controls - students
const nameInput = document.getElementById('searchName');
const deptSelect = document.getElementById('filterDept');
// filter controls - products
const productSearch = document.getElementById('searchProduct');
const categorySelect = document.getElementById('filterCategory');

let currentView = 'students'; // or 'products'


function renderStudents(list) {
    // clear any previous student results
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

function renderProducts(list) {
    productBody.innerHTML = '';
    list.forEach(p => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${p.name}</td>
            <td>${p.category}</td>
            <td class="price">$${p.price.toFixed(2)}</td>
            <td>${p.amount}</td>
            <td class="rating">${p.rating.toFixed(1)} &#9733;</td>
        `;
        productBody.appendChild(tr);
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

function populateCategories() {
    const cats = [...new Set(products.map(p => p.category))];
    cats.sort();
    cats.forEach(c => {
        const opt = document.createElement('option');
        opt.value = c;
        opt.textContent = c;
        categorySelect.appendChild(opt);
    });
}

function updateProductFilters() {
    const term = productSearch.value.trim().toLowerCase();
    const cat = categorySelect.value;
    const filtered = products.filter(p => {
        const matchName = p.name.toLowerCase().includes(term);
        const matchCat = cat === '' || p.category === cat;
        return matchName && matchCat;
    });
    renderProducts(filtered);
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
populateCategories();
nameInput.addEventListener('input', updateFilters);
deptSelect.addEventListener('change', updateFilters);
productSearch.addEventListener('input', updateProductFilters);
categorySelect.addEventListener('change', updateProductFilters);

function switchTo(view) {
    currentView = view;
    document.getElementById('student-section').style.display = view === 'students' ? '' : 'none';
    document.getElementById('product-section').style.display = view === 'products' ? '' : 'none';
    // clear previous results when switching
    if (view === 'students') {
        updateFilters();
    } else {
        updateProductFilters();
    }
    // update nav button active state
    document.querySelectorAll('#nav-buttons button').forEach(btn => {
        btn.classList.toggle('active', btn.textContent.toLowerCase().startsWith(view));
    });
}

// initial render (students view)
switchTo('students');
