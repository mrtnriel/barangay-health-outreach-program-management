/**
 * Barangay Health Outreach Management System
 * Front-end Interaction & Dynamic API Controller
 */

// 1. Navigation & Page Switching
const navLinks = document.querySelectorAll('.nav-link');
const pages = document.querySelectorAll('.page');
const breadcrumbTitle = document.getElementById('breadcrumb-title');

function showPage(name) {
  pages.forEach(page => page.classList.toggle('active', page.id === `${name}-page`));
  navLinks.forEach(link => link.classList.toggle('active', link.dataset.page === name));
  const activeLink = document.querySelector(`.nav-link[data-page="${name}"]`);
  if (breadcrumbTitle && activeLink) {
    const text = activeLink.textContent.replace(/^[^\w\s]+/, '').trim();
    breadcrumbTitle.textContent = text || 'Dashboard';
  }
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

navLinks.forEach(link => link.addEventListener('click', () => showPage(link.dataset.page)));
document.querySelectorAll('[data-go]').forEach(button =>
  button.addEventListener('click', () => showPage(button.dataset.go))
);

// Date display in topbar
const todayEl = document.getElementById('today');
if (todayEl) {
  todayEl.textContent = new Intl.DateTimeFormat('en-PH', {
    month: 'short', day: 'numeric', year: 'numeric'
  }).format(new Date());
}

// 2. Toast Notifications
const toast = document.getElementById('toast');
const showToast = (message, duration = 2800) => {
  if (!toast) return;
  toast.textContent = message;
  toast.classList.add('show');
  setTimeout(() => toast.classList.remove('show'), duration);
};

// 3. User Profile Dropdown
const profileAvatar = document.getElementById('top-profile-avatar');
const profileMenu = document.getElementById('profile-dropdown-menu');
const openChangePasswordBtn = document.getElementById('open-change-password');

if (profileAvatar && profileMenu) {
  profileAvatar.addEventListener('click', (e) => {
    e.stopPropagation();
    profileMenu.classList.toggle('open');
  });

  document.addEventListener('click', (e) => {
    if (!profileMenu.contains(e.target) && e.target !== profileAvatar) {
      profileMenu.classList.remove('open');
    }
  });
}

if (openChangePasswordBtn) {
  openChangePasswordBtn.addEventListener('click', () => {
    if (profileMenu) profileMenu.classList.remove('open');
    openModal('modal-password');
  });
}

// 4. Modal Manager
function openModal(id) {
  const el = document.getElementById(id);
  if (el) el.classList.add('open');
}

function closeModal(id) {
  const el = document.getElementById(id);
  if (el) el.classList.remove('open');
}

document.querySelectorAll('[data-close]').forEach(btn => {
  btn.addEventListener('click', () => closeModal(btn.dataset.close));
});

document.querySelectorAll('.modal-backdrop').forEach(backdrop => {
  backdrop.addEventListener('click', event => {
    if (event.target === backdrop) {
      backdrop.classList.remove('open');
    }
  });
});

const modalTriggers = {
  'add-resident': 'modal',
  'add-program': 'modal-program',
  'add-registration': 'modal-registration',
  'add-service': 'modal-service',
  'add-appointment': 'modal-appointment',
  'add-personnel': 'modal-personnel',
  'add-inventory': 'modal-inventory'
};

Object.entries(modalTriggers).forEach(([btnId, modalId]) => {
  const btn = document.getElementById(btnId);
  if (btn) {
    btn.addEventListener('click', () => {
      if (modalId === 'modal-service') {
        const dateInput = document.getElementById('service-date-input');
        if (dateInput && !dateInput.value) {
          const now = new Date();
          now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
          dateInput.value = now.toISOString().slice(0, 16);
        }
      }
      openModal(modalId);
    });
  }
});

const closeResidentBtn = document.getElementById('close-modal');
if (closeResidentBtn) closeResidentBtn.addEventListener('click', () => closeModal('modal'));
const cancelResidentBtn = document.getElementById('cancel-modal');
if (cancelResidentBtn) cancelResidentBtn.addEventListener('click', () => closeModal('modal'));

// 5. Form Submissions with Live Backend API Integration

// A. Add Resident Form
const residentForm = document.getElementById('resident-form');
if (residentForm) {
  residentForm.addEventListener('submit', async event => {
    event.preventDefault();
    const submitBtn = residentForm.querySelector('button[type="submit"]');
    if (submitBtn) { submitBtn.disabled = true; submitBtn.textContent = 'Saving...'; }

    try {
      const formData = new FormData(residentForm);
      const res = await fetch('../api/residents/index.php', { method: 'POST', body: formData });
      const result = await res.json();
      if (!res.ok || !result.success) throw new Error(result.error || 'Failed to save resident.');

      const r = result.data;
      const tbody = document.getElementById('resident-rows');
      if (tbody) {
        removeEmptyRow(tbody);
        const row = document.createElement('tr');
        row.dataset.residentRowId = r.resident_id;

        // Calculate age
        let ageText = '—';
        let badgeHtml = '';
        if (r.birth_date) {
          const bDate = new Date(r.birth_date);
          const age = new Date().getFullYear() - bDate.getFullYear();
          ageText = age + ' yrs';
          if (age >= 60) badgeHtml = '<span class="badge senior">60+ • Senior Citizen</span>';
          else if (age <= 12) badgeHtml = '<span class="badge pediatric">Child / Pediatric</span>';
          else badgeHtml = '<span class="badge adult">Adult</span>';
        }

        row.innerHTML = `
          <td><strong>${escapeHtml(r.full_name)}</strong><small>${escapeHtml(r.resident_code)}</small></td>
          <td>${escapeHtml(r.birth_date_formatted)}</td>
          <td><strong>${ageText}</strong>${badgeHtml ? '<br>' + badgeHtml : ''}</td>
          <td>${escapeHtml(r.sex)}</td>
          <td>${escapeHtml(r.address)}</td>
          <td>${escapeHtml(r.contact_number)}</td>
          <td>
            <button type="button" class="action-btn view-resident-btn" data-id="${r.resident_id}">
              <span>🔍</span> Profile
            </button>
          </td>
        `;
        tbody.prepend(row);
      }

      // Add to select dropdowns
      ['reg-resident-select', 'apt-resident-select'].forEach(selectId => {
        const select = document.getElementById(selectId);
        if (select) {
          const opt = document.createElement('option');
          opt.value = r.resident_id;
          opt.textContent = `${r.last_name}, ${r.first_name} (${r.resident_code})`;
          select.appendChild(opt);
        }
      });

      updateCounts();
      residentForm.reset();
      closeModal('modal');
      showToast('Resident record successfully saved!');
    } catch (err) {
      showToast(err.message || 'Error saving resident.');
    } finally {
      if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = 'Save resident'; }
    }
  });
}

// B. Create Program Form
const programForm = document.getElementById('program-form');
if (programForm) {
  programForm.addEventListener('submit', async event => {
    event.preventDefault();
    const submitBtn = programForm.querySelector('button[type="submit"]');
    if (submitBtn) { submitBtn.disabled = true; submitBtn.textContent = 'Creating...'; }

    try {
      const formData = new FormData(programForm);
      const res = await fetch('../api/programs/index.php', { method: 'POST', body: formData });
      const result = await res.json();
      if (!res.ok || !result.success) throw new Error(result.error || 'Failed to create program.');

      const p = result.data;
      const tbody = document.getElementById('program-rows');
      if (tbody) {
        removeEmptyRow(tbody);
        const row = document.createElement('tr');
        const statusClass = (p.program_status || 'upcoming').toLowerCase();
        row.innerHTML = `
          <td><strong>${escapeHtml(p.program_name)}</strong><small>${escapeHtml(p.description || '')}</small></td>
          <td>${escapeHtml(p.program_type)}</td>
          <td>${escapeHtml(p.start_date_formatted)}</td>
          <td>${escapeHtml(p.venue)}</td>
          <td>0 registered</td>
          <td><span class="status ${statusClass}">${escapeHtml(p.program_status)}</span></td>
        `;
        tbody.prepend(row);
      }

      const regProgSelect = document.getElementById('reg-program-select');
      if (regProgSelect) {
        const opt = document.createElement('option');
        opt.value = p.program_id;
        opt.textContent = `${p.program_name} [${p.program_status}]`;
        regProgSelect.appendChild(opt);
      }

      updateCounts();
      programForm.reset();
      closeModal('modal-program');
      showToast('Health program created successfully!');
    } catch (err) {
      showToast(err.message || 'Error creating program.');
    } finally {
      if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = 'Create program'; }
    }
  });
}

// C. New Registration Form
const regForm = document.getElementById('registration-form');
if (regForm) {
  regForm.addEventListener('submit', async event => {
    event.preventDefault();
    const submitBtn = regForm.querySelector('button[type="submit"]');
    if (submitBtn) { submitBtn.disabled = true; submitBtn.textContent = 'Enrolling...'; }

    try {
      const formData = new FormData(regForm);
      const res = await fetch('../api/registrations/index.php', { method: 'POST', body: formData });
      const result = await res.json();
      if (!res.ok || !result.success) throw new Error(result.error || 'Failed to register beneficiary.');

      const regId = result.registration_id;
      const resSelect = document.getElementById('reg-resident-select');
      const progSelect = document.getElementById('reg-program-select');
      const residentText = resSelect.options[resSelect.selectedIndex].text;
      const progText = progSelect.options[progSelect.selectedIndex].text.replace(/\s*\[.*\]$/, '');
      const status = formData.get('attendance_status') || 'Registered';
      const statusClass = status === 'Attended' ? 'ongoing' : 'upcoming';

      const tbody = document.getElementById('registration-rows');
      if (tbody) {
        removeEmptyRow(tbody);
        const row = document.createElement('tr');
        row.dataset.regRowId = regId;
        row.innerHTML = `
          <td><strong>${escapeHtml(residentText)}</strong></td>
          <td>${escapeHtml(progText)}</td>
          <td>Today</td>
          <td><span class="status ${statusClass}" id="status-reg-${regId}">${escapeHtml(status)}</span></td>
          <td>
            ${status === 'Registered' 
              ? `<button type="button" class="action-btn success mark-attended-btn" data-id="${regId}">✓ Mark Attended</button>` 
              : `<span class="muted" style="font-size: 10px; color:#16a34a; font-weight:600;">✓ Present</span>`}
          </td>
        `;
        tbody.prepend(row);
      }

      const srvSelect = document.getElementById('service-reg-select');
      if (srvSelect) {
        const opt = document.createElement('option');
        opt.value = regId;
        opt.textContent = `${residentText} — ${progText}`;
        srvSelect.appendChild(opt);
      }

      updateCounts();
      regForm.reset();
      closeModal('modal-registration');
      showToast('Beneficiary registered successfully!');
    } catch (err) {
      showToast(err.message || 'Error registering beneficiary.');
    } finally {
      if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = 'Confirm registration'; }
    }
  });
}

// D. Record Service Form
const serviceForm = document.getElementById('service-form');
if (serviceForm) {
  serviceForm.addEventListener('submit', async event => {
    event.preventDefault();
    const submitBtn = serviceForm.querySelector('button[type="submit"]');
    if (submitBtn) { submitBtn.disabled = true; submitBtn.textContent = 'Saving...'; }

    try {
      const formData = new FormData(serviceForm);
      const res = await fetch('../api/services/index.php', { method: 'POST', body: formData });
      const result = await res.json();
      if (!res.ok || !result.success) throw new Error(result.error || 'Failed to record service.');

      const regSelect = document.getElementById('service-reg-select');
      const regText = regSelect.options[regSelect.selectedIndex].text;
      const parts = regText.split('—').map(s => s.trim());
      const residentName = parts[0] || 'Beneficiary';
      const programName = parts[1] || 'Outreach Program';
      const desc = formData.get('service_description');
      const dateVal = formData.get('service_date');
      const dateFormatted = dateVal ? new Date(dateVal).toLocaleDateString('en-PH', { month:'short', day:'numeric', year:'numeric', hour:'numeric', minute:'2-digit' }) : 'Today';

      const tbody = document.getElementById('service-rows');
      if (tbody) {
        removeEmptyRow(tbody);
        const row = document.createElement('tr');
        row.innerHTML = `
          <td><strong>${escapeHtml(residentName)}</strong></td>
          <td>${escapeHtml(programName)}</td>
          <td>${escapeHtml(desc)}</td>
          <td>${escapeHtml(dateFormatted)}</td>
        `;
        tbody.prepend(row);
      }

      updateCounts();
      serviceForm.reset();
      closeModal('modal-service');
      showToast('Clinical service documentation recorded!');
    } catch (err) {
      showToast(err.message || 'Error recording service.');
    } finally {
      if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = 'Save service record'; }
    }
  });
}

// E. Book Appointment Form
const aptForm = document.getElementById('appointment-form');
if (aptForm) {
  aptForm.addEventListener('submit', async event => {
    event.preventDefault();
    const submitBtn = aptForm.querySelector('button[type="submit"]');
    if (submitBtn) { submitBtn.disabled = true; submitBtn.textContent = 'Booking...'; }

    try {
      const formData = new FormData(aptForm);
      const res = await fetch('../api/appointments/index.php', { method: 'POST', body: formData });
      const result = await res.json();
      if (!res.ok || !result.success) throw new Error(result.error || 'Failed to book appointment.');

      const a = result.data;
      const tbody = document.getElementById('appointment-rows');
      if (tbody) {
        removeEmptyRow(tbody);
        const row = document.createElement('tr');
        row.dataset.aptRowId = a.appointment_id;
        row.innerHTML = `
          <td><strong>${escapeHtml(a.resident_name)}</strong><small>${escapeHtml(a.resident_code)}</small></td>
          <td>${escapeHtml(a.purpose)}</td>
          <td>${escapeHtml(a.date_formatted)}</td>
          <td>${escapeHtml(a.physician_name)}</td>
          <td><span class="status upcoming" id="status-apt-${a.appointment_id}">Scheduled</span></td>
          <td>
            <button type="button" class="action-btn success complete-apt-btn" data-id="${a.appointment_id}">✓ Complete</button>
            <button type="button" class="action-btn danger cancel-apt-btn" data-id="${a.appointment_id}">✕ Cancel</button>
          </td>
        `;
        tbody.prepend(row);
      }

      updateCounts();
      aptForm.reset();
      closeModal('modal-appointment');
      showToast('Appointment booked successfully!');
    } catch (err) {
      showToast(err.message || 'Error booking appointment.');
    } finally {
      if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = 'Book appointment'; }
    }
  });
}

// F. Add Personnel Form
const personnelForm = document.getElementById('personnel-form');
if (personnelForm) {
  personnelForm.addEventListener('submit', async event => {
    event.preventDefault();
    const submitBtn = personnelForm.querySelector('button[type="submit"]');
    if (submitBtn) { submitBtn.disabled = true; submitBtn.textContent = 'Creating...'; }

    try {
      const formData = new FormData(personnelForm);
      const res = await fetch('../api/personnel/index.php', { method: 'POST', body: formData });
      const result = await res.json();
      if (!res.ok || !result.success) throw new Error(result.error || 'Failed to create personnel account.');

      const u = result.data;
      const tbody = document.getElementById('personnel-rows');
      if (tbody) {
        removeEmptyRow(tbody);
        const row = document.createElement('tr');
        row.innerHTML = `
          <td><strong>${escapeHtml(u.full_name)}</strong></td>
          <td><code style="background:#f4f6f4; padding:3px 6px; font-size:11px;">${escapeHtml(u.username)}</code></td>
          <td><span class="status ${u.role_class}">${escapeHtml(u.role)}</span></td>
          <td>Today</td>
          <td><span style="color:var(--green); font-size:11px; font-weight:600;">● Active</span></td>
        `;
        tbody.prepend(row);
      }

      updateCounts();
      personnelForm.reset();
      closeModal('modal-personnel');
      showToast('Personnel account created successfully!');
    } catch (err) {
      showToast(err.message || 'Error creating account.');
    } finally {
      if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = 'Create account'; }
    }
  });
}

// G. Add Inventory Form
const inventoryForm = document.getElementById('inventory-form');
if (inventoryForm) {
  inventoryForm.addEventListener('submit', async event => {
    event.preventDefault();
    const submitBtn = inventoryForm.querySelector('button[type="submit"]');
    if (submitBtn) { submitBtn.disabled = true; submitBtn.textContent = 'Saving...'; }

    try {
      const formData = new FormData(inventoryForm);
      const res = await fetch('../api/inventory/index.php', { method: 'POST', body: formData });
      const result = await res.json();
      if (!res.ok || !result.success) throw new Error(result.error || 'Failed to add inventory item.');

      const item = result.data;
      const tbody = document.getElementById('inventory-rows');
      if (tbody) {
        removeEmptyRow(tbody);
        const row = document.createElement('tr');
        row.dataset.itemRowId = item.item_id;
        row.innerHTML = `
          <td><strong>${escapeHtml(item.item_name)}</strong></td>
          <td>${escapeHtml(item.category)}</td>
          <td><strong id="stock-val-${item.item_id}">${Number(item.stock_quantity).toLocaleString()}</strong></td>
          <td>${escapeHtml(item.unit)}</td>
          <td><span class="status ${item.status_class}" id="stock-status-${item.item_id}">${escapeHtml(item.stock_status)}</span></td>
          <td>
            <button type="button" class="stock-btn quick-stock-btn" data-id="${item.item_id}" data-delta="10" title="Restock +10">+10</button>
            <button type="button" class="stock-btn quick-stock-btn" data-id="${item.item_id}" data-delta="-10" title="Dispense -10" style="color:#b91c1c;">-10</button>
            <button type="button" class="stock-btn quick-stock-btn" data-id="${item.item_id}" data-delta="50" title="Restock +50">+50</button>
          </td>
        `;
        tbody.prepend(row);
      }

      updateCounts();
      inventoryForm.reset();
      closeModal('modal-inventory');
      showToast('Inventory item added successfully!');
    } catch (err) {
      showToast(err.message || 'Error adding item.');
    } finally {
      if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = 'Save supply item'; }
    }
  });
}

// H. Change Password Form
const passwordForm = document.getElementById('password-form');
if (passwordForm) {
  passwordForm.addEventListener('submit', async event => {
    event.preventDefault();
    const submitBtn = passwordForm.querySelector('button[type="submit"]');
    if (submitBtn) { submitBtn.disabled = true; submitBtn.textContent = 'Updating...'; }

    try {
      const formData = new FormData(passwordForm);
      const res = await fetch('../api/auth/change-password.php', { method: 'POST', body: formData });
      const result = await res.json();
      if (!res.ok || !result.success) throw new Error(result.error || 'Failed to update password.');

      passwordForm.reset();
      closeModal('modal-password');
      showToast('Password updated successfully!');
    } catch (err) {
      showToast(err.message || 'Error updating password.');
    } finally {
      if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = 'Update password'; }
    }
  });
}

// 6. In-Table Contextual Actions (Event Delegation)
document.addEventListener('click', async event => {
  // A. Mark Attended in Registrations
  const attendedBtn = event.target.closest('.mark-attended-btn');
  if (attendedBtn) {
    const regId = attendedBtn.dataset.id;
    attendedBtn.disabled = true;
    attendedBtn.textContent = 'Updating...';

    try {
      const res = await fetch('../api/registrations/index.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'update_status', registration_id: regId, attendance_status: 'Attended' })
      });
      const result = await res.json();
      if (!res.ok || !result.success) throw new Error(result.error || 'Update failed');

      const statusBadge = document.getElementById(`status-reg-${regId}`);
      if (statusBadge) {
        statusBadge.textContent = 'Attended';
        statusBadge.className = 'status ongoing';
      }
      attendedBtn.parentElement.innerHTML = '<span class="muted" style="font-size: 10px; color:#16a34a; font-weight:600;">✓ Present</span>';
      showToast('Beneficiary marked as Attended.');
    } catch (err) {
      showToast(err.message || 'Failed to update attendance.');
      attendedBtn.disabled = false;
      attendedBtn.textContent = '✓ Mark Attended';
    }
    return;
  }

  // B. Complete / Cancel Appointment
  const completeAptBtn = event.target.closest('.complete-apt-btn');
  const cancelAptBtn = event.target.closest('.cancel-apt-btn');
  if (completeAptBtn || cancelAptBtn) {
    const btn = completeAptBtn || cancelAptBtn;
    const aptId = btn.dataset.id;
    const newStatus = completeAptBtn ? 'Completed' : 'Cancelled';
    btn.disabled = true;

    try {
      const res = await fetch('../api/appointments/index.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'update_status', appointment_id: aptId, status: newStatus })
      });
      const result = await res.json();
      if (!res.ok || !result.success) throw new Error(result.error || 'Update failed');

      const statusBadge = document.getElementById(`status-apt-${aptId}`);
      if (statusBadge) {
        statusBadge.textContent = newStatus;
        statusBadge.className = 'status completed';
      }
      btn.parentElement.innerHTML = `<span class="muted" style="font-size:10px;">${newStatus}</span>`;
      showToast(`Appointment marked as ${newStatus}.`);
    } catch (err) {
      showToast(err.message || 'Failed to update appointment.');
      btn.disabled = false;
    }
    return;
  }

  // C. Quick Stock Adjustment (+10, -10, +50)
  const stockBtn = event.target.closest('.quick-stock-btn');
  if (stockBtn) {
    const itemId = stockBtn.dataset.id;
    const delta = parseInt(stockBtn.dataset.delta, 10);
    stockBtn.disabled = true;

    try {
      const res = await fetch('../api/inventory/index.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'adjust_stock', item_id: itemId, delta: delta })
      });
      const result = await res.json();
      if (!res.ok || !result.success) throw new Error(result.error || 'Stock update failed');

      const valEl = document.getElementById(`stock-val-${itemId}`);
      const statusEl = document.getElementById(`stock-status-${itemId}`);
      if (valEl) valEl.textContent = Number(result.stock_quantity).toLocaleString();
      if (statusEl) {
        statusEl.textContent = result.stock_status;
        statusEl.className = `status ${result.status_class}`;
      }
      showToast(`Stock updated: ${result.stock_quantity} (${result.stock_status})`);
    } catch (err) {
      showToast(err.message || 'Failed to update stock.');
    } finally {
      stockBtn.disabled = false;
    }
    return;
  }

  // D. View Resident Profile Dossier
  const viewResBtn = event.target.closest('.view-resident-btn');
  if (viewResBtn) {
    const resId = viewResBtn.dataset.id;
    viewResBtn.disabled = true;

    try {
      const res = await fetch(`../api/residents/index.php?resident_id=${resId}`);
      const result = await res.json();
      if (!res.ok || !result.success) throw new Error(result.error || 'Could not load dossier.');

      const r = result.data;
      document.getElementById('prof-resident-name').textContent = r.full_name;
      document.getElementById('prof-resident-code').textContent = r.resident_code;
      document.getElementById('prof-age').textContent = r.age !== null ? `${r.age} yrs (${r.birth_date_formatted})` : '—';
      document.getElementById('prof-sex').textContent = r.sex;
      document.getElementById('prof-contact').textContent = r.contact_number || 'None provided';
      document.getElementById('prof-address').textContent = r.address;

      // Fill registrations
      const regTbody = document.getElementById('prof-registrations-tbody');
      if (regTbody) {
        if (!r.registrations || r.registrations.length === 0) {
          regTbody.innerHTML = '<tr><td colspan="3" class="muted" style="text-align:center;">No outreach registrations found.</td></tr>';
        } else {
          regTbody.innerHTML = r.registrations.map(reg => `
            <tr>
              <td><strong>${escapeHtml(reg.program_name)}</strong></td>
              <td>${new Date(reg.start_date).toLocaleDateString('en-PH', { month:'short', day:'numeric', year:'numeric' })}</td>
              <td><span class="status ${reg.attendance_status === 'Attended' ? 'ongoing' : 'upcoming'}">${escapeHtml(reg.attendance_status)}</span></td>
            </tr>
          `).join('');
        }
      }

      // Fill services
      const srvTbody = document.getElementById('prof-services-tbody');
      if (srvTbody) {
        if (!r.services || r.services.length === 0) {
          srvTbody.innerHTML = '<tr><td colspan="3" class="muted" style="text-align:center;">No medical services documented yet.</td></tr>';
        } else {
          srvTbody.innerHTML = r.services.map(s => `
            <tr>
              <td><strong>${escapeHtml(s.program_name)}</strong></td>
              <td>${escapeHtml(s.service_description)}</td>
              <td>${new Date(s.service_date).toLocaleDateString('en-PH', { month:'short', day:'numeric' })}</td>
            </tr>
          `).join('');
        }
      }

      openModal('modal-resident-profile');
    } catch (err) {
      showToast(err.message || 'Failed to load profile.');
    } finally {
      viewResBtn.disabled = false;
    }
  }
});

// 7. Reports CSV Export Functionality
const exportCsvBtn = document.getElementById('export-csv-btn');
if (exportCsvBtn) {
  exportCsvBtn.addEventListener('click', () => {
    exportAccomplishmentCSV();
  });
}

function exportAccomplishmentCSV() {
  const table = document.querySelector('#reports-page table');
  if (!table) {
    showToast('No report data found to export.');
    return;
  }

  const rows = table.querySelectorAll('tr');
  const csvData = [];

  rows.forEach(row => {
    const cols = row.querySelectorAll('th, td');
    const rowData = [];
    cols.forEach(col => {
      let text = col.innerText.replace(/"/g, '""').trim();
      rowData.push(`"${text}"`);
    });
    if (rowData.length > 0) {
      csvData.push(rowData.join(','));
    }
  });

  const csvString = '\uFEFF' + csvData.join('\r\n');
  const blob = new Blob([csvString], { type: 'text/csv;charset=utf-8;' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = `Barangay_Health_Accomplishment_Report_${new Date().toISOString().slice(0, 10)}.csv`;
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
  URL.revokeObjectURL(url);
  showToast('Report CSV downloaded successfully!');
}

// 8. Helpers: Count updates & Table Filters with "No Results" message
function removeEmptyRow(tbody) {
  const empty = tbody.querySelector('td[colspan]');
  if (empty) empty.parentElement.remove();
}

function updateCounts() {
  const updateBadge = (tbodyId, badgeId, suffix) => {
    const tbody = document.getElementById(tbodyId);
    const badge = document.getElementById(badgeId);
    if (tbody && badge) {
      const count = tbody.querySelectorAll('tr:not(.no-search-results):not(:has(td[colspan]))').length;
      badge.textContent = `${count} ${suffix}`;
    }
  };

  updateBadge('resident-rows', 'resident-count', 'residents');
  updateBadge('appointment-rows', 'appointment-count', 'scheduled appointments');
  updateBadge('inventory-rows', 'inventory-count', 'tracked items');
  updateBadge('personnel-rows', 'personnel-count', 'active personnel');

  const resTbody = document.getElementById('resident-rows');
  const statRes = document.getElementById('stat-residents-count');
  if (resTbody && statRes) {
    const count = resTbody.querySelectorAll('tr:not(.no-search-results):not(:has(td[colspan]))').length;
    statRes.textContent = count.toLocaleString();
  }
}

function setupTableFilter(inputId, rowSelector, tbodyId) {
  const input = document.getElementById(inputId);
  if (!input) return;
  input.addEventListener('input', event => {
    const query = event.target.value.toLowerCase().trim();
    const rows = document.querySelectorAll(rowSelector);
    let visibleCount = 0;

    rows.forEach(row => {
      if (row.classList.contains('no-search-results')) return;
      const match = query === '' || row.textContent.toLowerCase().includes(query);
      row.hidden = !match;
      if (match) visibleCount++;
    });

    const tbody = document.getElementById(tbodyId);
    if (tbody) {
      const existingMsg = tbody.querySelector('.no-search-results');
      if (existingMsg) existingMsg.remove();

      if (visibleCount === 0 && query !== '') {
        const noMatchRow = document.createElement('tr');
        noMatchRow.className = 'no-search-results';
        noMatchRow.innerHTML = `<td colspan="100%" style="text-align:center; padding: 25px; color: var(--muted);">No matching records found for "<strong>${escapeHtml(query)}</strong>"</td>`;
        tbody.appendChild(noMatchRow);
      }
    }
  });
}

setupTableFilter('resident-search', '#resident-rows tr', 'resident-rows');
setupTableFilter('program-search', '#program-rows tr', 'program-rows');
setupTableFilter('registration-search', '#registration-rows tr', 'registration-rows');
setupTableFilter('service-search', '#service-rows tr', 'service-rows');
setupTableFilter('appointment-search', '#appointment-rows tr', 'appointment-rows');
setupTableFilter('inventory-search', '#inventory-rows tr', 'inventory-rows');
setupTableFilter('personnel-search', '#personnel-rows tr', 'personnel-rows');

function escapeHtml(str) {
  if (!str) return '';
  const div = document.createElement('div');
  div.textContent = str;
  return div.innerHTML;
}
