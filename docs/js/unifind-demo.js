/*
 * UniFind — browser-only demo.
 * Replaces the original PHP/MySQL back end with localStorage so the app can be
 * tried on GitHub Pages. Each visitor gets their own copy of the sample data.
 * Page markup and styles.css are the original project's.
 */
(function () {
  var DB_KEY = 'uf_demo_db_v1';
  var SESSION_KEY = 'uf_demo_session_v1';
  var CATS = ['ID Card', 'Phone', 'Wallet', 'Keys', 'Bag', 'Other'];

  /* ---------- storage ---------- */
  function get(key) { try { return localStorage.getItem(key); } catch (e) { return null; } }
  function set(key, val) {
    try { localStorage.setItem(key, val); return true; }
    catch (e) { alert('Your browser storage is full or disabled, so this change could not be saved.'); return false; }
  }
  function del(key) { try { localStorage.removeItem(key); } catch (e) {} }
  function fresh() { return JSON.parse(JSON.stringify(window.UF_SEED)); }

  var db;
  try { db = JSON.parse(get(DB_KEY)) || fresh(); } catch (e) { db = fresh(); }
  function save() { return set(DB_KEY, JSON.stringify(db)); }
  function nextId(list, key) { return list.reduce(function (m, x) { return Math.max(m, x[key]); }, 0) + 1; }

  /* ---------- helpers ---------- */
  function $(sel) { return document.querySelector(sel); }
  function esc(s) {
    return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }
  function param(n) { return new URLSearchParams(location.search).get(n); }
  function go(url) { location.href = url; }
  function now() {
    var d = new Date(), p = function (n) { return String(n).padStart(2, '0'); };
    return d.getFullYear() + '-' + p(d.getMonth() + 1) + '-' + p(d.getDate()) + ' ' + p(d.getHours()) + ':' + p(d.getMinutes()) + ':' + p(d.getSeconds());
  }
  var validEmail = function (e) { return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(e); };
  var validPhone = function (p) { return /^05[0-9]{8}$/.test(p); };

  function showMessage(form, text, type) {
    var old = form.querySelector('.form-message');
    if (old) old.remove();
    if (!text) return;
    var p = document.createElement('p');
    p.className = 'form-message ' + (type || 'error');
    p.textContent = text;
    form.appendChild(p);
  }

  /* ---------- session ---------- */
  function session() { try { return JSON.parse(get(SESSION_KEY)); } catch (e) { return null; } }
  function signIn(role, id) { set(SESSION_KEY, JSON.stringify({ role: role, id: id })); }
  function requireRole(role) {
    var s = session();
    if (!s || s.role !== role) { location.replace('login.html'); throw new Error('redirect'); }
    return s.id;
  }
  window.addEventListener('error', function (e) { if (e.error && e.error.message === 'redirect') e.preventDefault(); });
  document.addEventListener('click', function (e) {
    var a = e.target.closest && e.target.closest('a[data-signout]');
    if (a) { e.preventDefault(); del(SESSION_KEY); go('index.html'); }
  });

  /* ---------- queries (mirror the original SQL) ---------- */
  function catName(id) { var c = db.categories.filter(function (x) { return x.catID === id; })[0]; return c ? c.catName : ''; }
  function catID(name) { var c = db.categories.filter(function (x) { return x.catName === name; })[0]; return c ? c.catID : 0; }
  function user(id) { return db.users.filter(function (u) { return u.userID === id; })[0]; }
  function report(id) { return db.reports.filter(function (r) { return r.reportID === id; })[0]; }
  function isSaved(userID, reportID) { return db.saved.some(function (s) { return s.userID === userID && s.reportID === reportID; }); }
  function byNewest(a, b) { return a.date < b.date ? 1 : a.date > b.date ? -1 : b.reportID - a.reportID; }

  function searchReports(search, category, sort, withOwner) {
    var q = search.toLowerCase();
    var list = db.reports.filter(function (r) {
      var owner = user(r.userID);
      if (withOwner && !owner) return false;
      if (category !== 'all' && catName(r.catID) !== category) return false;
      if (!q) return true;
      var fields = [r.item_name, r.description, r.location, catName(r.catID)];
      if (withOwner) fields.push(owner.email);
      return fields.some(function (f) { return String(f || '').toLowerCase().indexOf(q) !== -1; });
    });
    list.sort(byNewest);
    if (sort === 'oldest') list.reverse();
    return list;
  }

  /* ---------- shared markup (same as the PHP output) ---------- */
  function meta(label, value) {
    return '\n                  <div class="meta">\n                    <strong>' + label + ':</strong>\n                    ' + esc(value) + '\n                  </div>';
  }
  function card(r, opts) {
    var html = '\n                <div class="report-card">\n                  <img\n                    src="' + esc(r.imageURL) + '"\n                    alt="' + esc(r.item_name) + '"\n                  />';
    html += '\n                  <h3>' + esc(r.item_name) + '</h3>';
    html += meta('Category', catName(r.catID)) + meta('Date', r.date) + meta('Location', r.location);
    if (opts.owner) html += meta('Owner', (user(r.userID) || {}).email);
    html += '\n                  <p>' + esc(r.description) + '</p>\n                  <div class="actions">' + opts.actions(r) + '\n                  </div>\n                </div>';
    return html;
  }
  function link(href, cls, text, extra) {
    return '\n                    <a\n                      href="' + href + '"\n                      class="' + cls + '"' + (extra || '') + '\n                    >\n                      ' + text + '\n                    </a>';
  }
  function renderList(el, reports, opts, empty) {
    el.innerHTML = reports.length ? reports.map(function (r) { return card(r, opts); }).join('') + '\n            '
      : '\n              <div class="empty-state">' + empty + '</div>\n            ';
  }

  /* ---------- report form (add / edit / admin edit) ---------- */
  function readImage(file) {
    return new Promise(function (resolve) {
      if (!file) return resolve('');
      var url = URL.createObjectURL(file), im = new Image();
      im.onload = function () {
        var k = Math.min(1, 900 / Math.max(im.width, im.height)), c = document.createElement('canvas');
        c.width = Math.round(im.width * k); c.height = Math.round(im.height * k);
        var ctx = c.getContext('2d'); ctx.fillStyle = '#fff'; ctx.fillRect(0, 0, c.width, c.height);
        ctx.drawImage(im, 0, 0, c.width, c.height);
        URL.revokeObjectURL(url); resolve(c.toDataURL('image/jpeg', 0.82));
      };
      im.onerror = function () { URL.revokeObjectURL(url); resolve(''); };
      im.src = url;
    });
  }
  function validImageType(file) { return ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'].indexOf(file.type) !== -1; }

  function reportFormErrors(f, imageRequired) {
    var v = function (n) { return f.elements[n].value.trim(); };
    var file = f.elements.image.files[0];
    if (v('itemName') === '') return 'Item name is required.';
    if (v('itemName').length < 2) return 'Item name must be at least 2 characters.';
    if (v('category') === '') return 'Please select a category.';
    if (v('reportDate') === '') return 'Date is required.';
    if (v('location') === '') return 'Location is required.';
    if (v('location').length < 2) return 'Location must be at least 2 characters.';
    if (v('description') === '') return 'Description is required.';
    if (v('description').length < 10) return 'Description must be at least 10 characters.';
    if (v('phone') === '') return 'Phone number is required.';
    if (!validPhone(v('phone'))) return 'Phone number must start with 05 and contain 10 digits.';
    if (imageRequired && !file) return 'Please upload an image for the report.';
    if (file && !validImageType(file)) return 'Only JPG, PNG, JPEG, or WEBP images are allowed.';
    return '';
  }
  function fillReportForm(f, r) {
    f.elements.itemName.value = r.item_name;
    f.elements.category.value = catName(r.catID);
    f.elements.reportDate.value = r.date;
    f.elements.location.value = r.location;
    f.elements.description.value = r.description;
    f.elements.phone.value = r.phone;
    var im = document.getElementById('currentImage');
    if (im) im.src = r.imageURL;
  }
  function handleReportForm(f, opts) {
    f.addEventListener('submit', function (e) {
      e.preventDefault();
      var err = reportFormErrors(f, opts.imageRequired);
      if (err) return showMessage(f, err);
      var v = function (n) { return f.elements[n].value.trim(); };
      readImage(f.elements.image.files[0]).then(function (img) {
        var r = opts.report || { reportID: nextId(db.reports, 'reportID'), userID: opts.userID, adminID: null };
        r.item_name = v('itemName'); r.catID = catID(v('category')); r.date = v('reportDate');
        r.location = v('location'); r.description = v('description'); r.phone = v('phone');
        if (img) r.imageURL = img;
        if (opts.adminID) r.adminID = opts.adminID;
        if (!opts.report) db.reports.push(r);
        if (!save()) return;
        go(opts.next);
      });
    });
  }

  /* ---------- pages ---------- */
  var pages = {
    login: function () {
      var f = document.querySelector('form');
      var card = document.querySelector('.auth-card');
      var box = document.createElement('div');
      box.className = 'demo-box';
      box.innerHTML = '<strong>Try the demo</strong> — sign in with a sample account (password <strong>demo1234</strong>), or create your own. ' +
        'Everything stays in your browser.' +
        '<div class="demo-actions"><button type="button" class="secondary-btn" data-demo="sara@unifind.demo">Sign in as a student</button>' +
        '<button type="button" class="secondary-btn" data-demo="admin@unifind.demo">Sign in as admin</button></div>' +
        '<button type="button" class="demo-reset">Reset demo data</button>';
      card.appendChild(box);

      function attempt(email, password) {
        if (email === '') return showMessage(f, 'Email address is required.');
        if (!validEmail(email)) return showMessage(f, 'Please enter a valid email address.');
        if (password === '') return showMessage(f, 'Password is required.');
        var a = db.admins.filter(function (x) { return x.email === email; })[0];
        if (a) {
          if (password !== a.password) return showMessage(f, 'Incorrect password. Please try again.');
          signIn('admin', a.adminID); return go('admin-reports.html');
        }
        var u = db.users.filter(function (x) { return x.email === email; })[0];
        if (!u) return showMessage(f, 'No account found with this email. Please sign up first.');
        if (password !== u.password) return showMessage(f, 'Incorrect password. Please try again.');
        signIn('user', u.userID); go('reports.html');
      }
      f.addEventListener('submit', function (e) {
        e.preventDefault();
        attempt(f.elements.loginEmail.value.trim(), f.elements.loginPassword.value.trim());
      });
      box.querySelectorAll('[data-demo]').forEach(function (b) {
        b.addEventListener('click', function () {
          f.elements.loginEmail.value = b.getAttribute('data-demo');
          f.elements.loginPassword.value = 'demo1234';
          attempt(f.elements.loginEmail.value, 'demo1234');
        });
      });
      box.querySelector('.demo-reset').addEventListener('click', function () {
        if (confirm('Reset all demo reports, saved items and accounts to the original sample data?')) {
          db = fresh(); save(); del(SESSION_KEY); showMessage(f, 'Demo data has been reset.', 'success');
        }
      });
    },

    register: function () {
      var f = document.getElementById('registerForm');
      f.addEventListener('submit', function (e) {
        e.preventDefault();
        var v = function (n) { return f.elements[n].value.trim(); };
        var name = v('registerName'), email = v('registerEmail'), phone = v('registerPhone'), pw = v('registerPassword');
        var err = name === '' ? 'Full name is required.'
          : name.length < 3 ? 'Full name must be at least 3 characters.'
          : email === '' ? 'Email address is required.'
          : !validEmail(email) ? 'Please enter a valid email address.'
          : phone === '' ? 'Phone number is required.'
          : !validPhone(phone) ? 'Phone number must start with 05 and contain 10 digits.'
          : pw === '' ? 'Password is required.'
          : pw.length < 6 ? 'Password must be at least 6 characters.'
          : db.users.some(function (u) { return u.email === email; }) ? 'This email is already registered. Please sign in instead.' : '';
        if (err) return showMessage(f, err);
        var parts = name.split(' '), first = parts.shift();
        var u = { userID: nextId(db.users, 'userID'), firstName: first, lastName: parts.join(' ') || '-', email: email, password: pw, phone: phone };
        db.users.push(u);
        if (!save()) return showMessage(f, 'Registration failed. Please try again.');
        signIn('user', u.userID); go('reports.html');
      });
    },

    reports: function () {
      var me = requireRole('user');
      var search = (param('search') || '').trim(), category = (param('category') || 'all').trim(), sort = (param('sort') || 'newest').trim();
      $('#searchInput').value = search; $('#categoryFilter').value = category; $('#sortFilter').value = sort;
      var el = $('#reportsList');
      function render() {
        renderList(el, searchReports(search, category, sort, false), {
          actions: function (r) {
            var saved = isSaved(me, r.reportID);
            return link('report-details.html?id=' + r.reportID, 'primary-btn', 'View') +
              link('#', 'secondary-btn', saved ? 'Unsave' : 'Save', ' data-toggle-save="' + r.reportID + '"');
          }
        }, 'No reports found.');
      }
      render();
      el.addEventListener('click', function (e) {
        var a = e.target.closest('[data-toggle-save]');
        if (!a) return;
        e.preventDefault();
        toggleSave(me, parseInt(a.getAttribute('data-toggle-save'), 10));
        render();
      });
    },

    'report-details': function () {
      var me = requireRole('user');
      var id = parseInt(param('id'), 10);
      if (!(id > 0)) return location.replace('reports.html');
      var r = report(id);
      if (!r) { document.body.textContent = 'Report not found.'; return; }
      var isOwner = r.userID === me;
      function p(label, value) { return '\n              <p><strong>' + label + ':</strong> ' + esc(value) + '</p>'; }
      function render() {
        var saved = isSaved(me, id);
        $('#reportDetails').innerHTML =
          '\n          <div class="details-card">\n            <div>\n              <img\n                class="details-image"\n                src="' + esc(r.imageURL) + '"\n                alt="' + esc(r.item_name) + '"\n              />\n            </div>' +
          '\n            <div class="details-content">\n              <h1>' + esc(r.item_name) + '</h1>' +
          p('Category', catName(r.catID)) + p('Date', r.date) + p('Location', r.location) + p('Description', r.description) + p('Phone Number', r.phone) +
          '\n              <div class="actions">' +
          link('#', 'secondary-btn', saved ? 'Unsave' : 'Save', ' data-toggle-save') +
          '\n                <a href="reports.html" class="secondary-btn">Back</a>' +
          (isOwner ? '\n                  <a href="edit-report.html?id=' + id + '" class="primary-btn">\n                    Edit\n                  </a>' +
            link('#', 'secondary-btn', 'Delete', ' data-delete') : '') +
          '\n              </div>\n            </div>\n          </div>\n        ';
      }
      render();
      $('#reportDetails').addEventListener('click', function (e) {
        if (e.target.closest('[data-toggle-save]')) { e.preventDefault(); toggleSave(me, id); render(); }
        if (e.target.closest('[data-delete]')) {
          e.preventDefault();
          if (confirm('Are you sure you want to delete this report?')) { deleteReport(id); go('my-reports.html'); }
        }
      });
    },

    'add-report': function () {
      var me = requireRole('user');
      handleReportForm(document.getElementById('reportForm'), { userID: me, imageRequired: true, next: 'my-reports.html' });
    },

    'my-reports': function () {
      var me = requireRole('user');
      var el = $('#myReportsList');
      function render() {
        var mine = db.reports.filter(function (r) { return r.userID === me; }).sort(byNewest);
        renderList(el, mine, {
          actions: function (r) {
            return link('report-details.html?id=' + r.reportID, 'primary-btn', 'View') +
              link('edit-report.html?id=' + r.reportID, 'secondary-btn', 'Edit') +
              link('#', 'secondary-btn', 'Delete', ' data-delete="' + r.reportID + '"');
          }
        }, 'You have not submitted any reports yet.');
      }
      render();
      el.addEventListener('click', function (e) {
        var a = e.target.closest('[data-delete]');
        if (!a) return;
        e.preventDefault();
        if (confirm('Are you sure you want to delete this report?')) { deleteReport(parseInt(a.getAttribute('data-delete'), 10)); render(); }
      });
    },

    saved: function () {
      var me = requireRole('user');
      var el = $('#savedList');
      function render() {
        var list = db.saved.filter(function (s) { return s.userID === me; })
          .sort(function (a, b) { return a.savedAt < b.savedAt ? 1 : a.savedAt > b.savedAt ? -1 : 0; })
          .map(function (s) { return report(s.reportID); }).filter(Boolean);
        renderList(el, list, {
          actions: function (r) {
            return link('report-details.html?id=' + r.reportID, 'primary-btn', 'View') +
              link('#', 'secondary-btn', 'Remove', ' data-unsave="' + r.reportID + '"');
          }
        }, 'No saved reports yet.');
      }
      render();
      el.addEventListener('click', function (e) {
        var a = e.target.closest('[data-unsave]');
        if (!a) return;
        e.preventDefault();
        toggleSave(me, parseInt(a.getAttribute('data-unsave'), 10)); render();
      });
    },

    'edit-report': function () {
      var me = requireRole('user');
      var id = parseInt(param('id'), 10);
      if (!(id > 0)) return location.replace('my-reports.html');
      var r = report(id);
      if (!r || r.userID !== me) { document.body.textContent = 'Report not found or unauthorized.'; return; }
      var f = document.querySelector('main form');
      fillReportForm(f, r);
      handleReportForm(f, { report: r, next: 'my-reports.html' });
    },

    profile: function () {
      var me = requireRole('user');
      var u = user(me);
      var f = document.getElementById('profileForm');
      var name = $('#profileName'), email = $('#profileEmail'), phone = $('#profilePhone');
      var editBtn = $('#editProfileBtn'), saveBtn = $('#saveProfileBtn');
      function show(n, e, p) {
        $('#profileDisplayName').textContent = n; name.value = n; email.value = e; phone.value = p;
        [name, email, phone].forEach(function (i) { i.disabled = true; });
        editBtn.classList.remove('hidden'); saveBtn.classList.add('hidden');
      }
      show((u.firstName + ' ' + u.lastName).trim(), u.email, u.phone);
      editBtn.addEventListener('click', function () {
        [name, email, phone].forEach(function (i) { i.disabled = false; });
        editBtn.classList.add('hidden'); saveBtn.classList.remove('hidden');
      });
      f.addEventListener('submit', function (e) {
        e.preventDefault();
        var n = name.value.trim(), em = email.value.trim(), ph = phone.value.trim();
        var err = n === '' ? 'Full name is required.'
          : n.length < 3 ? 'Full name must be at least 3 characters.'
          : em === '' ? 'Email address is required.'
          : !validEmail(em) ? 'Please enter a valid email address.'
          : ph === '' ? 'Phone number is required.'
          : !validPhone(ph) ? 'Phone number must start with 05 and contain 10 digits.'
          : db.users.some(function (x) { return x.email === em && x.userID !== me; }) ? 'This email is already used by another account.' : '';
        if (err) { show(n, em, ph); return showMessage(f, err); }
        var parts = n.split(' ');
        u.firstName = parts.shift(); u.lastName = parts.join(' ') || '-'; u.email = em; u.phone = ph;
        if (!save()) return showMessage(f, 'Failed to update profile. Please try again.');
        show(n, em, ph);
        showMessage(f, 'Profile updated successfully.', 'success');
      });
    },

    'admin-reports': function () {
      requireRole('admin');
      var search = (param('search') || '').trim(), category = (param('category') || 'all').trim(), sort = (param('sort') || 'newest').trim();
      $('#adminSearchInput').value = search; $('#adminCategoryFilter').value = category; $('#adminSortFilter').value = sort;
      var el = $('#adminReportsList');
      function render() {
        renderList(el, searchReports(search, category, sort, true), {
          owner: true,
          actions: function (r) {
            return link('admin-edit-report.html?id=' + r.reportID, 'primary-btn', 'Edit') +
              link('#', 'secondary-btn', 'Delete', ' data-delete="' + r.reportID + '"');
          }
        }, 'No reports found.');
      }
      render();
      el.addEventListener('click', function (e) {
        var a = e.target.closest('[data-delete]');
        if (!a) return;
        e.preventDefault();
        if (confirm('Are you sure you want to delete this report?')) { deleteReport(parseInt(a.getAttribute('data-delete'), 10)); render(); }
      });
    },

    'admin-edit-report': function () {
      var adminID = requireRole('admin');
      var id = parseInt(param('id'), 10);
      if (!(id > 0)) return location.replace('admin-reports.html');
      var r = report(id);
      if (!r) { document.body.textContent = 'Report not found.'; return; }
      var f = document.querySelector('main form');
      fillReportForm(f, r);
      handleReportForm(f, { report: r, adminID: adminID, next: 'admin-reports.html' });
    }
  };

  function toggleSave(userID, reportID) {
    if (isSaved(userID, reportID)) {
      db.saved = db.saved.filter(function (s) { return !(s.userID === userID && s.reportID === reportID); });
    } else {
      db.saved.push({ userID: userID, reportID: reportID, savedAt: now() });
    }
    save();
  }
  function deleteReport(id) {
    db.reports = db.reports.filter(function (r) { return r.reportID !== id; });
    db.saved = db.saved.filter(function (s) { return s.reportID !== id; });
    save();
  }

  var page = document.body.getAttribute('data-page');
  if (pages[page]) pages[page]();
})();
