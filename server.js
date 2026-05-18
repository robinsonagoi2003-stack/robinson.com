const express = require('express');
const sqlite3 = require('sqlite3').verbose();
const bcrypt = require('bcryptjs');
const path = require('path');

const app = express();
const PORT = process.env.PORT || 3000;
const dbPath = path.join(__dirname, 'eventhub.db');

app.use(express.json());
app.use(express.urlencoded({ extended: true }));
app.use(express.static(__dirname));

const db = new sqlite3.Database(dbPath, (err) => {
  if (err) {
    console.error('Unable to open database:', err.message);
    process.exit(1);
  }
});

db.serialize(() => {
  db.run(
    `CREATE TABLE IF NOT EXISTS users (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      name TEXT NOT NULL,
      email TEXT NOT NULL UNIQUE,
      passwordHash TEXT NOT NULL,
      company TEXT,
      createdAt DATETIME DEFAULT CURRENT_TIMESTAMP
    )`
  );
});

function sendError(res, status, message) {
  return res.status(status).json({ success: false, message });
}

app.post('/api/register', (req, res) => {
  const { name, email, password, company } = req.body;

  if (!name || !email || !password) {
    return sendError(res, 400, 'Name, email, and password are required.');
  }

  const trimmedEmail = email.trim().toLowerCase();

  db.get('SELECT id FROM users WHERE email = ?', [trimmedEmail], (err, row) => {
    if (err) {
      return sendError(res, 500, 'Database error during registration.');
    }

    if (row) {
      return sendError(res, 409, 'An account with this email already exists.');
    }

    const passwordHash = bcrypt.hashSync(password, 10);

    db.run(
      'INSERT INTO users (name, email, passwordHash, company) VALUES (?, ?, ?, ?)',
      [name.trim(), trimmedEmail, passwordHash, company ? company.trim() : null],
      function (insertErr) {
        if (insertErr) {
          return sendError(res, 500, 'Unable to save registration data.');
        }

        return res.json({
          success: true,
          message: 'Registration successful. You can now log in.',
          user: {
            id: this.lastID,
            name: name.trim(),
            email: trimmedEmail,
            company: company ? company.trim() : null,
          },
        });
      }
    );
  });
});

app.post('/api/login', (req, res) => {
  const { email, password } = req.body;

  if (!email || !password) {
    return sendError(res, 400, 'Email and password are required.');
  }

  const trimmedEmail = email.trim().toLowerCase();

  db.get('SELECT id, name, email, company, passwordHash FROM users WHERE email = ?', [trimmedEmail], (err, user) => {
    if (err) {
      return sendError(res, 500, 'Database error during login.');
    }

    if (!user || !bcrypt.compareSync(password, user.passwordHash)) {
      return sendError(res, 401, 'Invalid email or password.');
    }

    return res.json({
      success: true,
      message: 'Login successful.',
      user: {
        id: user.id,
        name: user.name,
        email: user.email,
        company: user.company,
      },
    });
  });
});

app.get('*', (req, res) => {
  res.sendFile(path.join(__dirname, 'business.html'));
});

app.listen(PORT, () => {
  console.log(`EventHub Business Platform server running on http://localhost:${PORT}`);
});