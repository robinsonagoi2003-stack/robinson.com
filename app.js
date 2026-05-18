const loginTab = document.getElementById('loginTab');
const registerTab = document.getElementById('registerTab');
const loginForm = document.getElementById('loginForm');
const registerForm = document.getElementById('registerForm');
const dashboard = document.getElementById('dashboard');
const loginMessage = document.getElementById('loginMessage');
const registerMessage = document.getElementById('registerMessage');
const profileName = document.getElementById('profileName');
const profileEmail = document.getElementById('profileEmail');
const profileCompany = document.getElementById('profileCompany');
const logoutButton = document.getElementById('logoutButton');

function toggleTab(tab) {
  if (tab === 'login') {
    loginTab.classList.add('active');
    registerTab.classList.remove('active');
    loginForm.classList.remove('hidden');
    registerForm.classList.add('hidden');
  } else {
    loginTab.classList.remove('active');
    registerTab.classList.add('active');
    loginForm.classList.add('hidden');
    registerForm.classList.remove('hidden');
  }
}

function setMessage(container, text, type = 'success') {
  container.textContent = text;
  container.className = `message ${type}`;
  container.classList.remove('hidden');
}

function clearMessage(container) {
  container.textContent = '';
  container.className = 'message hidden';
}

async function submitForm(url, payload) {
  const response = await fetch(url, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload),
  });

  return response.json();
}

function showDashboard(user) {
  profileName.textContent = user.name;
  profileEmail.textContent = user.email;
  profileCompany.textContent = user.company || 'Not specified';
  dashboard.classList.remove('hidden');
  loginForm.classList.add('hidden');
  registerForm.classList.add('hidden');
  loginTab.classList.remove('active');
  registerTab.classList.remove('active');
}

function hideDashboard() {
  dashboard.classList.add('hidden');
  toggleTab('login');
}

async function handleLogin(event) {
  event.preventDefault();
  clearMessage(loginMessage);

  const email = document.getElementById('loginEmail').value.trim();
  const password = document.getElementById('loginPassword').value;

  const payload = { email, password };
  const result = await submitForm('/api/login', payload);

  if (result.success) {
    localStorage.setItem('eventhubUser', JSON.stringify(result.user));
    showDashboard(result.user);
    setMessage(loginMessage, result.message, 'success');
  } else {
    setMessage(loginMessage, result.message || 'Login failed.', 'error');
  }
}

async function handleRegister(event) {
  event.preventDefault();
  clearMessage(registerMessage);

  const name = document.getElementById('registerName').value.trim();
  const company = document.getElementById('registerCompany').value.trim();
  const email = document.getElementById('registerEmail').value.trim();
  const password = document.getElementById('registerPassword').value;

  const payload = { name, company, email, password };
  const result = await submitForm('/api/register', payload);

  if (result.success) {
    setMessage(registerMessage, result.message, 'success');
    document.getElementById('registerFormElement').reset();
    toggleTab('login');
  } else {
    setMessage(registerMessage, result.message || 'Registration failed.', 'error');
  }
}

function restoreSession() {
  const stored = localStorage.getItem('eventhubUser');
  if (stored) {
    try {
      const user = JSON.parse(stored);
      if (user && user.name && user.email) {
        showDashboard(user);
      }
    } catch (error) {
      localStorage.removeItem('eventhubUser');
    }
  }
}

function logout() {
  localStorage.removeItem('eventhubUser');
  hideDashboard();
}

document.getElementById('loginFormElement').addEventListener('submit', handleLogin);
document.getElementById('registerFormElement').addEventListener('submit', handleRegister);
loginTab.addEventListener('click', () => toggleTab('login'));
registerTab.addEventListener('click', () => toggleTab('register'));
logoutButton.addEventListener('click', logout);

restoreSession();
