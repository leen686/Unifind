const DEFAULT_IMAGE =
  "https://via.placeholder.com/600x400/eaf2ff/2f66e7?text=No+Image";

/* =========================
   STORAGE HELPERS
========================= */
function getUsers() {
  return JSON.parse(localStorage.getItem("users")) || [];
}

function setUsers(users) {
  localStorage.setItem("users", JSON.stringify(users));
}

function getAdmins() {
  return JSON.parse(localStorage.getItem("admins")) || [];
}

function setAdmins(admins) {
  localStorage.setItem("admins", JSON.stringify(admins));
}

function getCurrentUser() {
  return JSON.parse(localStorage.getItem("currentUser")) || null;
}

function setCurrentUser(user) {
  localStorage.setItem("currentUser", JSON.stringify(user));
}

function getReports() {
  const reports = JSON.parse(localStorage.getItem("reports"));
  if (reports && reports.length) return reports;

  const sampleReports = [
    {
      id: 1001,
      ownerEmail: "student1@ksu.edu.sa",
      itemName: "Student ID Card",
      category: "ID Card",
      description: "Blue KSU student ID card lost near the main entrance.",
      date: "2026-03-20",
      location: "Building 31",
      phone: "0500000001",
      image: DEFAULT_IMAGE,
    },
    {
      id: 1002,
      ownerEmail: "student1@ksu.edu.sa",
      itemName: "Black Wallet",
      category: "Wallet",
      description: "Found near the library study area.",
      date: "2026-03-21",
      location: "Library",
      phone: "0500000002",
      image: DEFAULT_IMAGE,
    },
    {
      id: 1003,
      ownerEmail: "student2@ksu.edu.sa",
      itemName: "Car Keys",
      category: "Keys",
      description: "Set of silver keys with a blue keychain.",
      date: "2026-03-18",
      location: "Parking Area",
      phone: "0500000003",
      image: DEFAULT_IMAGE,
    },
    {
      id: 1004,
      ownerEmail: "student3@ksu.edu.sa",
      itemName: "Backpack",
      category: "Bag",
      description: "Black backpack left in the hallway near classroom 204.",
      date: "2026-03-17",
      location: "Building 5",
      phone: "0500000004",
      image: DEFAULT_IMAGE,
    },
  ];

  localStorage.setItem("reports", JSON.stringify(sampleReports));
  return sampleReports;
}

function setReports(reports) {
  localStorage.setItem("reports", JSON.stringify(reports));
}

function getSavedReports() {
  const saved = JSON.parse(localStorage.getItem("savedReports"));
  if (saved && saved.length) return saved;

  const defaultSaved = [1001, 1003];
  localStorage.setItem("savedReports", JSON.stringify(defaultSaved));
  return defaultSaved;
}

function setSavedReports(savedReports) {
  localStorage.setItem("savedReports", JSON.stringify(savedReports));
}

/* =========================
   INITIAL SEED
========================= */
function seedAdminAccount() {
  const admins = getAdmins();
  const exists = admins.some((admin) => admin.email === "admin@unifind.com");

  if (!exists) {
    admins.push({
      name: "Administrator",
      email: "admin@unifind.com",
      password: "admin123",
      role: "admin",
    });
    setAdmins(admins);
  }
}

function seedDemoUser() {
  const users = getUsers();
  const exists = users.some((user) => user.email === "student1@ksu.edu.sa");

  if (!exists) {
    users.push({
      name: "Sara Alqahtani",
      email: "student1@ksu.edu.sa",
      phone: "0551234567",
      password: "123456",
      role: "user",
    });

    users.push({
      name: "Nora Alharbi",
      email: "student2@ksu.edu.sa",
      phone: "0559876543",
      password: "123456",
      role: "user",
    });

    setUsers(users);
  }
}

/* =========================
   AUTH
========================= */
function logout() {
  localStorage.removeItem("currentUser");
  window.location.href = "index.html";
}

function setupAuthLinks() {
  const authLink = document.getElementById("authLink");
  if (authLink) {
    const currentUser = getCurrentUser();
    if (currentUser) {
      authLink.textContent = "Sign Out";
      authLink.href = "#";
      authLink.addEventListener("click", function (e) {
        e.preventDefault();
        logout();
      });
    } else {
      authLink.textContent = "Sign In";
      authLink.href = "login.html";
    }
  }

  const adminAuthLink = document.getElementById("adminAuthLink");
  if (adminAuthLink) {
    adminAuthLink.textContent = "Sign Out";
    adminAuthLink.href = "#";
    adminAuthLink.addEventListener("click", function (e) {
      e.preventDefault();
      logout();
    });
  }

  const logoutBtn = document.getElementById("logoutBtn");
  if (logoutBtn) {
    logoutBtn.addEventListener("click", function () {
      logout();
    });
  }
}

function requireUserRole() {
  const currentUser = getCurrentUser();
  if (!currentUser || currentUser.role !== "user") {
    alert("This page is for users only.");
    window.location.href = "login.html";
    return false;
  }
  return true;
}

function requireAdminRole() {
  const currentUser = getCurrentUser();
  if (!currentUser || currentUser.role !== "admin") {
    alert("This page is for admins only.");
    window.location.href = "login.html";
    return false;
  }
  return true;
}

/* =========================
   FILE / IMAGE HELPER
========================= */
function readImage(file, callback) {
  if (!file) {
    callback(DEFAULT_IMAGE);
    return;
  }

  const reader = new FileReader();
  reader.onload = function (e) {
    callback(e.target.result);
  };
  reader.readAsDataURL(file);
}

/* =========================
   LOGIN
========================= */
function setupLoginForm() {
  const loginForm = document.getElementById("loginForm");
  if (!loginForm) return;

  loginForm.addEventListener("submit", function (e) {
    e.preventDefault();

    const email = document.getElementById("loginEmail").value.trim();
    const password = document.getElementById("loginPassword").value.trim();
    const role = document.getElementById("loginRole").value;
    const message = document.getElementById("loginMessage");

    if (role === "admin") {
      const admins = getAdmins();
      const admin = admins.find(
        (a) => a.email === email && a.password === password,
      );

      if (!admin) {
        message.textContent = "Invalid admin email or password.";
        return;
      }

      setCurrentUser(admin);
      message.textContent = "Admin login successful. Redirecting...";
      setTimeout(() => {
        window.location.href = "admin-dashboard.html";
      }, 700);
      return;
    }

    const users = getUsers();
    const user = users.find(
      (u) => u.email === email && u.password === password && u.role === "user",
    );

    if (!user) {
      message.textContent = "Invalid email or password.";
      return;
    }

    setCurrentUser(user);
    message.textContent = "Login successful. Redirecting...";
    setTimeout(() => {
      window.location.href = "reports.html";
    }, 700);
  });
}

/* =========================
   REGISTER
========================= */
function setupRegisterForm() {
  const registerForm = document.getElementById("registerForm");
  if (!registerForm) return;

  registerForm.addEventListener("submit", function (e) {
    e.preventDefault();

    const name = document.getElementById("registerName").value.trim();
    const email = document.getElementById("registerEmail").value.trim();
    const phone = document.getElementById("registerPhone").value.trim();
    const password = document.getElementById("registerPassword").value.trim();
    const message = document.getElementById("registerMessage");

    const users = getUsers();
    const emailExists = users.some((user) => user.email === email);

    if (emailExists) {
      message.textContent = "This email is already registered.";
      return;
    }

    const newUser = {
      name,
      email,
      phone,
      password,
      role: "user",
    };

    users.push(newUser);
    setUsers(users);
    setCurrentUser(newUser);

    message.textContent = "Account created successfully. Redirecting...";
    setTimeout(() => {
      window.location.href = "reports.html";
    }, 700);
  });
}

/* =========================
   CARDS
========================= */
function createUserReportCard(report, options = {}) {
  const {
    canManage = false,
    showSave = true,
    showRemoveSaved = false,
  } = options;

  const savedReports = getSavedReports();
  const isSaved = savedReports.includes(report.id);

  return `
    <div class="report-card">
      <img src="${report.image || DEFAULT_IMAGE}" alt="${report.itemName}">
      <h3>${report.itemName}</h3>
      <div class="meta"><strong>Category:</strong> ${report.category}</div>
      <div class="meta"><strong>Date:</strong> ${report.date}</div>
      <div class="meta"><strong>Location:</strong> ${report.location}</div>
      <p>${report.description}</p>

      <div class="actions">
        <a href="report-details.html?id=${report.id}" class="primary-btn">View</a>

        ${
          showSave
            ? showRemoveSaved
              ? `<button class="secondary-btn" onclick="removeSavedReport(${report.id})">Remove</button>`
              : `<button class="secondary-btn" onclick="toggleSaveReport(${report.id})">${isSaved ? "Unsave" : "Save"}</button>`
            : ""
        }

        ${
          canManage
            ? `
              <a href="edit-report.html?id=${report.id}" class="secondary-btn">Edit</a>
              <button class="secondary-btn" onclick="deleteOwnReport(${report.id})">Delete</button>
            `
            : ""
        }
      </div>
    </div>
  `;
}

function createAdminReportCard(report) {
  return `
    <div class="report-card">
      <img src="${report.image || DEFAULT_IMAGE}" alt="${report.itemName}">
      <h3>${report.itemName}</h3>
      <div class="meta"><strong>Category:</strong> ${report.category}</div>
      <div class="meta"><strong>Date:</strong> ${report.date}</div>
      <div class="meta"><strong>Location:</strong> ${report.location}</div>
      <div class="meta"><strong>Owner:</strong> ${report.ownerEmail}</div>
      <p>${report.description}</p>

      <div class="actions">
        <a href="admin-edit-report.html?id=${report.id}" class="primary-btn">Edit</a>
        <button class="secondary-btn" onclick="deleteAnyReport(${report.id})">Delete</button>
      </div>
    </div>
  `;
}

/* =========================
   SAVE
========================= */
function toggleSaveReport(reportId) {
  if (!requireUserRole()) return;

  let savedReports = getSavedReports();

  if (savedReports.includes(reportId)) {
    savedReports = savedReports.filter((id) => id !== reportId);
  } else {
    savedReports.push(reportId);
  }

  setSavedReports(savedReports);

  if (document.getElementById("savedList")) {
    renderSavedReportsPage();
  } else {
    window.location.reload();
  }
}

function removeSavedReport(reportId) {
  if (!requireUserRole()) return;

  const updated = getSavedReports().filter((id) => id !== reportId);
  setSavedReports(updated);
  renderSavedReportsPage();
}

/* =========================
   REPORTS PAGE
========================= */
function renderReportsPage() {
  const reportsList = document.getElementById("reportsList");
  if (!reportsList) return;
  if (!requireUserRole()) return;

  const searchInput = document.getElementById("searchInput");
  const categoryFilter = document.getElementById("categoryFilter");
  const sortFilter = document.getElementById("sortFilter");

  function drawReports() {
    let reports = [...getReports()];
    const searchText = searchInput.value.trim().toLowerCase();
    const selectedCategory = categoryFilter.value;
    const selectedSort = sortFilter.value;

    if (searchText) {
      reports = reports.filter(
        (report) =>
          report.itemName.toLowerCase().includes(searchText) ||
          report.description.toLowerCase().includes(searchText) ||
          report.location.toLowerCase().includes(searchText) ||
          report.category.toLowerCase().includes(searchText),
      );
    }

    if (selectedCategory !== "all") {
      reports = reports.filter(
        (report) => report.category === selectedCategory,
      );
    }

    reports.sort((a, b) => {
      if (selectedSort === "newest") {
        return new Date(b.date) - new Date(a.date);
      }
      return new Date(a.date) - new Date(b.date);
    });

    if (!reports.length) {
      reportsList.innerHTML = `<div class="empty-state">No reports found.</div>`;
      return;
    }

    reportsList.innerHTML = reports
      .map((report) => createUserReportCard(report))
      .join("");
  }

  searchInput.addEventListener("input", drawReports);
  categoryFilter.addEventListener("change", drawReports);
  sortFilter.addEventListener("change", drawReports);

  drawReports();
}

/* =========================
   ADD REPORT
========================= */
function setupAddReportForm() {
  const reportForm = document.getElementById("reportForm");
  if (!reportForm) return;
  if (!requireUserRole()) return;

  reportForm.addEventListener("submit", function (e) {
    e.preventDefault();

    const currentUser = getCurrentUser();
    const itemName = document.getElementById("itemName").value.trim();
    const category = document.getElementById("category").value;
    const description = document.getElementById("description").value.trim();
    const date = document.getElementById("reportDate").value;
    const location = document.getElementById("location").value.trim();
    const phone = document.getElementById("phone").value.trim();
    const imageFile = document.getElementById("image").files[0];
    const reportMessage = document.getElementById("reportMessage");

    readImage(imageFile, function (imageData) {
      const reports = getReports();

      const newReport = {
        id: Date.now(),
        ownerEmail: currentUser.email,
        itemName,
        category,
        description,
        date,
        location,
        phone,
        image: imageData || DEFAULT_IMAGE,
      };

      reports.unshift(newReport);
      setReports(reports);

      reportMessage.textContent = "Report submitted successfully.";
      reportForm.reset();

      setTimeout(() => {
        window.location.href = "my-reports.html";
      }, 700);
    });
  });
}

/* =========================
   DETAILS
========================= */
function renderReportDetailsPage() {
  const reportDetails = document.getElementById("reportDetails");
  if (!reportDetails) return;
  if (!requireUserRole()) return;

  const reportId = new URLSearchParams(window.location.search).get("id");
  const reports = getReports();
  const report = reports.find((item) => String(item.id) === String(reportId));

  if (!report) {
    reportDetails.innerHTML = `<div class="empty-state">Report not found.</div>`;
    return;
  }

  const currentUser = getCurrentUser();
  const isOwner = currentUser.email === report.ownerEmail;
  const isSaved = getSavedReports().includes(report.id);

  reportDetails.innerHTML = `
    <div class="details-card">
      <div>
        <img class="details-image" src="${report.image || DEFAULT_IMAGE}" alt="${report.itemName}">
      </div>

      <div class="details-content">
        <h1>${report.itemName}</h1>
        <p><strong>Category:</strong> ${report.category}</p>
        <p><strong>Date:</strong> ${report.date}</p>
        <p><strong>Location:</strong> ${report.location}</p>
        <p><strong>Description:</strong> ${report.description}</p>
        <p><strong>Phone Number:</strong> ${report.phone}</p>

        <div class="actions">
          <button class="secondary-btn" onclick="toggleSaveReport(${report.id})">
            ${isSaved ? "Unsave" : "Save"}
          </button>
          <a href="reports.html" class="secondary-btn">Back</a>
          ${
            isOwner
              ? `
                <a href="edit-report.html?id=${report.id}" class="primary-btn">Edit</a>
                <button class="secondary-btn" onclick="deleteOwnReport(${report.id})">Delete</button>
              `
              : ""
          }
        </div>
      </div>
    </div>
  `;
}

/* =========================
   SAVED
========================= */
function renderSavedReportsPage() {
  const savedList = document.getElementById("savedList");
  if (!savedList) return;
  if (!requireUserRole()) return;

  const savedIds = getSavedReports();
  const reports = getReports().filter((report) => savedIds.includes(report.id));

  if (!reports.length) {
    savedList.innerHTML = `<div class="empty-state">No saved reports yet.</div>`;
    return;
  }

  savedList.innerHTML = reports
    .map((report) =>
      createUserReportCard(report, {
        canManage: false,
        showSave: true,
        showRemoveSaved: true,
      }),
    )
    .join("");
}

/* =========================
   MY REPORTS
========================= */
function renderMyReportsPage() {
  const myReportsList = document.getElementById("myReportsList");
  if (!myReportsList) return;
  if (!requireUserRole()) return;

  const currentUser = getCurrentUser();
  const reports = getReports().filter(
    (report) => report.ownerEmail === currentUser.email,
  );

  if (!reports.length) {
    myReportsList.innerHTML = `<div class="empty-state">You have not submitted any reports yet.</div>`;
    return;
  }

  myReportsList.innerHTML = reports
    .map((report) =>
      createUserReportCard(report, {
        canManage: true,
        showSave: false,
        showRemoveSaved: false,
      }),
    )
    .join("");
}

/* =========================
   DELETE OWN
========================= */
function deleteOwnReport(reportId) {
  const currentUser = getCurrentUser();
  if (!currentUser || currentUser.role !== "user") return;

  const reports = getReports();
  const report = reports.find((item) => item.id === reportId);

  if (!report || report.ownerEmail !== currentUser.email) {
    alert("You can only delete your own reports.");
    return;
  }

  const updatedReports = reports.filter((item) => item.id !== reportId);
  setReports(updatedReports);

  const updatedSaved = getSavedReports().filter((id) => id !== reportId);
  setSavedReports(updatedSaved);

  window.location.href = "my-reports.html";
}

/* =========================
   EDIT OWN
========================= */
function setupEditReportForm() {
  const editReportForm = document.getElementById("editReportForm");
  if (!editReportForm) return;
  if (!requireUserRole()) return;

  const reportId = new URLSearchParams(window.location.search).get("id");
  const currentUser = getCurrentUser();
  const reports = getReports();
  const report = reports.find((item) => String(item.id) === String(reportId));

  if (!report || report.ownerEmail !== currentUser.email) {
    alert("You can only edit your own reports.");
    window.location.href = "my-reports.html";
    return;
  }

  document.getElementById("editItemName").value = report.itemName;
  document.getElementById("editCategory").value = report.category;
  document.getElementById("editReportDate").value = report.date;
  document.getElementById("editLocation").value = report.location;
  document.getElementById("editDescription").value = report.description;
  document.getElementById("editPhone").value = report.phone;

  editReportForm.addEventListener("submit", function (e) {
    e.preventDefault();

    const imageFile = document.getElementById("editImage").files[0];
    const editMessage = document.getElementById("editMessage");

    readImage(imageFile, function (imageData) {
      report.itemName = document.getElementById("editItemName").value.trim();
      report.category = document.getElementById("editCategory").value;
      report.date = document.getElementById("editReportDate").value;
      report.location = document.getElementById("editLocation").value.trim();
      report.description = document
        .getElementById("editDescription")
        .value.trim();
      report.phone = document.getElementById("editPhone").value.trim();

      if (imageFile) {
        report.image = imageData;
      }

      setReports(reports);
      editMessage.textContent = "Report updated successfully.";

      setTimeout(() => {
        window.location.href = "my-reports.html";
      }, 700);
    });
  });
}

/* =========================
   PROFILE
========================= */
function setupProfileForm() {
  const profileForm = document.getElementById("profileForm");
  if (!profileForm) return;
  if (!requireUserRole()) return;

  const currentUser = getCurrentUser();
  document.getElementById("profileName").value =
    currentUser.name || "Sara Alqahtani";
  document.getElementById("profileEmail").value =
    currentUser.email || "student1@ksu.edu.sa";
  document.getElementById("profilePhone").value =
    currentUser.phone || "0551234567";

  profileForm.addEventListener("submit", function (e) {
    e.preventDefault();

    const users = getUsers();
    const userIndex = users.findIndex(
      (user) => user.email === currentUser.email,
    );

    if (userIndex === -1) return;

    users[userIndex].name = document.getElementById("profileName").value.trim();
    users[userIndex].phone = document
      .getElementById("profilePhone")
      .value.trim();

    setUsers(users);
    setCurrentUser(users[userIndex]);

    const profileMessage = document.getElementById("profileMessage");
    profileMessage.textContent = "Profile updated successfully.";
  });
}

/* =========================
   ADMIN REPORTS
========================= */
function renderAdminReportsPage() {
  const adminReportsList = document.getElementById("adminReportsList");
  if (!adminReportsList) return;
  if (!requireAdminRole()) return;

  const adminSearchInput = document.getElementById("adminSearchInput");
  const adminCategoryFilter = document.getElementById("adminCategoryFilter");
  const adminSortFilter = document.getElementById("adminSortFilter");

  function drawAdminReports() {
    let reports = [...getReports()];
    const searchText = adminSearchInput.value.trim().toLowerCase();
    const selectedCategory = adminCategoryFilter.value;
    const selectedSort = adminSortFilter.value;

    if (searchText) {
      reports = reports.filter(
        (report) =>
          report.itemName.toLowerCase().includes(searchText) ||
          report.description.toLowerCase().includes(searchText) ||
          report.location.toLowerCase().includes(searchText) ||
          report.category.toLowerCase().includes(searchText),
      );
    }

    if (selectedCategory !== "all") {
      reports = reports.filter(
        (report) => report.category === selectedCategory,
      );
    }

    reports.sort((a, b) => {
      if (selectedSort === "newest") {
        return new Date(b.date) - new Date(a.date);
      }
      return new Date(a.date) - new Date(b.date);
    });

    if (!reports.length) {
      adminReportsList.innerHTML = `<div class="empty-state">No reports found.</div>`;
      return;
    }

    adminReportsList.innerHTML = reports
      .map((report) => createAdminReportCard(report))
      .join("");
  }

  adminSearchInput.addEventListener("input", drawAdminReports);
  adminCategoryFilter.addEventListener("change", drawAdminReports);
  adminSortFilter.addEventListener("change", drawAdminReports);

  drawAdminReports();
}

/* =========================
   ADMIN DELETE
========================= */
function deleteAnyReport(reportId) {
  if (!requireAdminRole()) return;

  const reports = getReports().filter((report) => report.id !== reportId);
  setReports(reports);

  const updatedSaved = getSavedReports().filter((id) => id !== reportId);
  setSavedReports(updatedSaved);

  window.location.reload();
}

/* =========================
   ADMIN EDIT
========================= */
function setupAdminEditReportForm() {
  const adminEditReportForm = document.getElementById("adminEditReportForm");
  if (!adminEditReportForm) return;
  if (!requireAdminRole()) return;

  const reportId = new URLSearchParams(window.location.search).get("id");
  const reports = getReports();
  const report = reports.find((item) => String(item.id) === String(reportId));

  if (!report) {
    alert("Report not found.");
    window.location.href = "admin-reports.html";
    return;
  }

  document.getElementById("adminEditItemName").value = report.itemName;
  document.getElementById("adminEditCategory").value = report.category;
  document.getElementById("adminEditReportDate").value = report.date;
  document.getElementById("adminEditLocation").value = report.location;
  document.getElementById("adminEditDescription").value = report.description;
  document.getElementById("adminEditPhone").value = report.phone;

  adminEditReportForm.addEventListener("submit", function (e) {
    e.preventDefault();

    const imageFile = document.getElementById("adminEditImage").files[0];
    const adminEditMessage = document.getElementById("adminEditMessage");

    readImage(imageFile, function (imageData) {
      report.itemName = document
        .getElementById("adminEditItemName")
        .value.trim();
      report.category = document.getElementById("adminEditCategory").value;
      report.date = document.getElementById("adminEditReportDate").value;
      report.location = document
        .getElementById("adminEditLocation")
        .value.trim();
      report.description = document
        .getElementById("adminEditDescription")
        .value.trim();
      report.phone = document.getElementById("adminEditPhone").value.trim();

      if (imageFile) {
        report.image = imageData;
      }

      setReports(reports);
      adminEditMessage.textContent = "Report updated successfully.";

      setTimeout(() => {
        window.location.href = "admin-reports.html";
      }, 700);
    });
  });
}

/* =========================
   INIT
========================= */
document.addEventListener("DOMContentLoaded", function () {
  seedAdminAccount();
  seedDemoUser();
  getReports();
  getSavedReports();

  setupAuthLinks();
  setupLoginForm();
  setupRegisterForm();

  renderReportsPage();
  setupAddReportForm();
  renderReportDetailsPage();
  renderSavedReportsPage();
  renderMyReportsPage();
  setupEditReportForm();
  setupProfileForm();

  renderAdminReportsPage();
  setupAdminEditReportForm();
});
