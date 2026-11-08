<?php
/**
 * Global Footer & Modals Partial
 * Barangay Health Outreach Management System
 */

// Preload residents, programs, and registrations for modal dropdowns
$dropdownResidents = [];
$dropdownPrograms = [];
$dropdownRegistrations = [];

if (isset($pdo)) {
    try {
        $dropdownResidents = $pdo->query("SELECT resident_id, first_name, last_name, address FROM residents ORDER BY last_name ASC")->fetchAll();
        $dropdownPrograms = $pdo->query("SELECT program_id, program_name, program_status FROM programs WHERE program_status != 'Cancelled' ORDER BY start_date DESC")->fetchAll();
        $dropdownRegistrations = $pdo->query("
            SELECT reg.registration_id, r.first_name, r.last_name, p.program_name
            FROM registrations reg
            JOIN residents r ON reg.resident_id = r.resident_id
            JOIN programs p ON reg.program_id = p.program_id
            ORDER BY reg.registration_date DESC
        ")->fetchAll();
    } catch (PDOException $e) {
        error_log("Footer dropdown preload error: " . $e->getMessage());
    }
}
?>
    <footer>Barangay Health Outreach Management System <span>•</span> Health Center Workspace</footer>
  </main>

  <!-- 1. Add Resident Modal -->
  <div class="modal-backdrop" id="modal">
    <form class="modal" id="resident-form">
      <div class="modal-heading">
        <div>
          <p class="eyebrow">RESIDENT RECORD</p>
          <h2>Add resident</h2>
        </div>
        <button type="button" class="close-button" id="close-modal">×</button>
      </div>

      <label>First name
        <input name="firstName" required placeholder="Enter first name">
      </label>

      <label>Last name
        <input name="lastName" required placeholder="Enter last name">
      </label>

      <div class="form-row">
        <label>Date of birth
          <input name="birthDate" type="date">
        </label>
        <label>Sex
          <select name="sex" required>
            <option value="">Select</option>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
          </select>
        </label>
      </div>

      <label>Address
        <input name="address" required placeholder="Enter complete address">
      </label>

      <label>Contact number
        <input name="contact" placeholder="Enter contact number (e.g. 09XX XXX XXXX)">
      </label>

      <div class="modal-actions">
        <button type="button" class="button secondary" id="cancel-modal">Cancel</button>
        <button class="button primary" type="submit">Save resident</button>
      </div>
    </form>
  </div>

  <!-- 2. Create Health Program Modal -->
  <div class="modal-backdrop" id="modal-program">
    <form class="modal" id="program-form">
      <div class="modal-heading">
        <div>
          <p class="eyebrow">OUTREACH PROGRAM</p>
          <h2>Create health program</h2>
        </div>
        <button type="button" class="close-button" data-close="modal-program">×</button>
      </div>

      <label>Program name
        <input name="program_name" required placeholder="e.g. Community Medical Mission">
      </label>

      <div class="form-row">
        <label>Program type
          <select name="program_type" required>
            <option value="Medical Mission">Medical Mission</option>
            <option value="Free Service">Free Service</option>
            <option value="Consultation">Consultation</option>
            <option value="Health Education">Health Education</option>
            <option value="Vaccination">Vaccination</option>
            <option value="Dental Outreach">Dental Outreach</option>
          </select>
        </label>
        <label>Initial status
          <select name="program_status" required>
            <option value="Upcoming">Upcoming</option>
            <option value="Ongoing">Ongoing</option>
          </select>
        </label>
      </div>

      <label>Venue / Location
        <input name="venue" required placeholder="e.g. Barangay Health Center, Covered Court">
      </label>

      <div class="form-row">
        <label>Start date & schedule
          <input name="start_date" type="datetime-local" required>
        </label>
        <label>End date (optional)
          <input name="end_date" type="datetime-local">
        </label>
      </div>

      <label>Description & notes
        <textarea name="description" placeholder="Brief overview of services and medical deliverables..."></textarea>
      </label>

      <div class="modal-actions">
        <button type="button" class="button secondary" data-close="modal-program">Cancel</button>
        <button class="button primary" type="submit">Create program</button>
      </div>
    </form>
  </div>

  <!-- 3. New Registration Modal -->
  <div class="modal-backdrop" id="modal-registration">
    <form class="modal" id="registration-form">
      <div class="modal-heading">
        <div>
          <p class="eyebrow">BENEFICIARY ENROLLMENT</p>
          <h2>New registration</h2>
        </div>
        <button type="button" class="close-button" data-close="modal-registration">×</button>
      </div>

      <label>Select resident / beneficiary
        <select name="resident_id" id="reg-resident-select" required>
          <option value="">Choose resident...</option>
          <?php foreach ($dropdownResidents as $dr): 
            $code = 'RES-' . str_pad($dr['resident_id'], 4, '0', STR_PAD_LEFT);
          ?>
            <option value="<?= (int)$dr['resident_id'] ?>">
              <?= htmlspecialchars($dr['last_name'] . ', ' . $dr['first_name'] . ' (' . $code . ')') ?>
            </option>
          <?php endforeach; ?>
        </select>
      </label>

      <label>Select health outreach program
        <select name="program_id" id="reg-program-select" required>
          <option value="">Choose program...</option>
          <?php foreach ($dropdownPrograms as $dp): ?>
            <option value="<?= (int)$dp['program_id'] ?>">
              <?= htmlspecialchars($dp['program_name'] . ' [' . $dp['program_status'] . ']') ?>
            </option>
          <?php endforeach; ?>
        </select>
      </label>

      <label>Initial attendance status
        <select name="attendance_status" required>
          <option value="Registered">Registered</option>
          <option value="Attended">Attended</option>
        </select>
      </label>

      <div class="modal-actions">
        <button type="button" class="button secondary" data-close="modal-registration">Cancel</button>
        <button class="button primary" type="submit">Confirm registration</button>
      </div>
    </form>
  </div>

  <!-- 4. Record Service Modal -->
  <div class="modal-backdrop" id="modal-service">
    <form class="modal" id="service-form">
      <div class="modal-heading">
        <div>
          <p class="eyebrow">CLINICAL DOCUMENTATION</p>
          <h2>Record health service</h2>
        </div>
        <button type="button" class="close-button" data-close="modal-service">×</button>
      </div>

      <label>Select registered beneficiary
        <select name="registration_id" id="service-reg-select" required>
          <option value="">Choose beneficiary registration...</option>
          <?php foreach ($dropdownRegistrations as $dreg): ?>
            <option value="<?= (int)$dreg['registration_id'] ?>">
              <?= htmlspecialchars($dreg['first_name'] . ' ' . $dreg['last_name'] . ' — ' . $dreg['program_name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </label>

      <label>Service details, diagnosis & medicines dispensed
        <textarea name="service_description" required placeholder="e.g. General checkup, blood pressure reading (120/80), dispensed 30 tablets Amlodipine 5mg..."></textarea>
      </label>

      <label>Service date & time
        <input name="service_date" type="datetime-local" id="service-date-input" required>
      </label>

      <div class="modal-actions">
        <button type="button" class="button secondary" data-close="modal-service">Cancel</button>
        <button class="button primary" type="submit">Save service record</button>
      </div>
    </form>
  </div>

  <!-- 5. Book Appointment Modal -->
  <div class="modal-backdrop" id="modal-appointment">
    <form class="modal" id="appointment-form">
      <div class="modal-heading">
        <div>
          <p class="eyebrow">CLINIC SCHEDULING</p>
          <h2>Book consultation appointment</h2>
        </div>
        <button type="button" class="close-button" data-close="modal-appointment">×</button>
      </div>

      <label>Select patient / resident
        <select name="resident_id" id="apt-resident-select" required>
          <option value="">Choose resident...</option>
          <?php foreach ($dropdownResidents as $dr): 
            $code = 'RES-' . str_pad($dr['resident_id'], 4, '0', STR_PAD_LEFT);
          ?>
            <option value="<?= (int)$dr['resident_id'] ?>">
              <?= htmlspecialchars($dr['last_name'] . ', ' . $dr['first_name'] . ' (' . $code . ')') ?>
            </option>
          <?php endforeach; ?>
        </select>
      </label>

      <label>Purpose / Medical concern
        <input name="purpose" required placeholder="e.g. Hypertension follow-up, prenatal consultation">
      </label>

      <div class="form-row">
        <label>Appointment date & time
          <input name="appointment_date" type="datetime-local" required>
        </label>
        <label>Attending physician
          <input name="physician_name" value="Dr. Maria Ramos" placeholder="Physician or Officer name">
        </label>
      </div>

      <div class="modal-actions">
        <button type="button" class="button secondary" data-close="modal-appointment">Cancel</button>
        <button class="button primary" type="submit">Book appointment</button>
      </div>
    </form>
  </div>

  <!-- 6. Add Personnel Modal -->
  <div class="modal-backdrop" id="modal-personnel">
    <form class="modal" id="personnel-form">
      <div class="modal-heading">
        <div>
          <p class="eyebrow">STAFF ACCOUNT</p>
          <h2>Add personnel</h2>
        </div>
        <button type="button" class="close-button" data-close="modal-personnel">×</button>
      </div>

      <label>Full name & title
        <input name="full_name" required placeholder="e.g. Juanita Dela Cruz, RN">
      </label>

      <label>Username
        <input name="username" required placeholder="e.g. jdelacruz">
      </label>

      <label>Initial password
        <input name="password" type="password" required minlength="6" placeholder="Minimum 6 characters">
      </label>

      <label>Assigned role
        <select name="role" required>
          <option value="Staff">Barangay Staff</option>
          <option value="BHW">Barangay Health Worker (BHW)</option>
          <option value="Admin">System Administrator</option>
        </select>
      </label>

      <div class="modal-actions">
        <button type="button" class="button secondary" data-close="modal-personnel">Cancel</button>
        <button class="button primary" type="submit">Create account</button>
      </div>
    </form>
  </div>

  <!-- 7. Add Inventory Item Modal -->
  <div class="modal-backdrop" id="modal-inventory">
    <form class="modal" id="inventory-form">
      <div class="modal-heading">
        <div>
          <p class="eyebrow">SUPPLY REGISTER</p>
          <h2>Add inventory item</h2>
        </div>
        <button type="button" class="close-button" data-close="modal-inventory">×</button>
      </div>

      <label>Item name & strength
        <input name="item_name" required placeholder="e.g. Amoxicillin 500mg Capsules">
      </label>

      <label>Category
        <select name="category" required>
          <option value="Hypertension Maintenance">Hypertension Maintenance</option>
          <option value="Vitamins & Supplements">Vitamins & Supplements</option>
          <option value="Antibiotics">Antibiotics</option>
          <option value="Analgesic / Antipyretic">Analgesic / Antipyretic</option>
          <option value="Medical Equipment / Kits">Medical Equipment / Kits</option>
          <option value="Diagnostic Consumables">Diagnostic Consumables</option>
          <option value="General Supplies">General Supplies</option>
        </select>
      </label>

      <div class="form-row">
        <label>Available stock quantity
          <input name="stock_quantity" type="number" min="0" required value="100">
        </label>
        <label>Unit of measure
          <input name="unit" required placeholder="e.g. Tablets, Bottles, Kits, Strips" value="Tablets">
        </label>
      </div>

      <label>Stock status
        <select name="stock_status" required>
          <option value="Adequate">Adequate</option>
          <option value="Low Stock">Low Stock</option>
          <option value="Out of Stock">Out of Stock</option>
        </select>
      </label>

      <div class="modal-actions">
        <button type="button" class="button secondary" data-close="modal-inventory">Cancel</button>
        <button class="button primary" type="submit">Save supply item</button>
      </div>
    </form>
  </div>

  <!-- 8. Change Password Modal -->
  <div class="modal-backdrop" id="modal-password">
    <form class="modal" id="password-form" style="max-width: 400px;">
      <div class="modal-heading">
        <div>
          <p class="eyebrow">SECURITY SETTINGS</p>
          <h2>Change password</h2>
        </div>
        <button type="button" class="close-button" data-close="modal-password">×</button>
      </div>

      <label>Current password
        <input name="current_password" type="password" required placeholder="Enter current password">
      </label>

      <label>New password
        <input name="new_password" type="password" required minlength="6" placeholder="Minimum 6 characters">
      </label>

      <label>Confirm new password
        <input name="confirm_password" type="password" required minlength="6" placeholder="Re-enter new password">
      </label>

      <div class="modal-actions">
        <button type="button" class="button secondary" data-close="modal-password">Cancel</button>
        <button class="button primary" type="submit">Update password</button>
      </div>
    </form>
  </div>

  <!-- 9. Resident Profile & History Modal -->
  <div class="modal-backdrop" id="modal-resident-profile">
    <div class="modal" style="max-width: 620px;">
      <div class="modal-heading">
        <div>
          <p class="eyebrow">RESIDENT HEALTH DOSSIER</p>
          <h2 id="prof-resident-name">Resident Profile</h2>
          <span id="prof-resident-code" class="muted" style="font-size:11px;">RES-0000</span>
        </div>
        <button type="button" class="close-button" data-close="modal-resident-profile">×</button>
      </div>

      <div id="prof-details-box" style="background:#fafbfa; border:1px solid var(--line); padding:14px; margin-bottom:18px;">
        <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:10px; font-size:11px;">
          <div><span class="muted">Age & Category:</span><br><strong id="prof-age">—</strong></div>
          <div><span class="muted">Sex:</span><br><strong id="prof-sex">—</strong></div>
          <div><span class="muted">Contact:</span><br><strong id="prof-contact">—</strong></div>
          <div style="grid-column: span 3;"><span class="muted">Residential Address:</span><br><strong id="prof-address">—</strong></div>
        </div>
      </div>

      <div style="margin-bottom:14px;">
        <h3 style="font-size:12px; margin:0 0 8px; color:var(--green-dark);">Outreach Program Registrations</h3>
        <div class="table-wrap" style="max-height:140px; overflow-y:auto; border:1px solid var(--line);">
          <table>
            <thead><tr><th>PROGRAM</th><th>SCHEDULE</th><th>ATTENDANCE</th></tr></thead>
            <tbody id="prof-registrations-tbody"><tr><td colspan="3" class="muted">Loading...</td></tr></tbody>
          </table>
        </div>
      </div>

      <div style="margin-bottom:14px;">
        <h3 style="font-size:12px; margin:0 0 8px; color:var(--green-dark);">Clinical Services & Treatments Received</h3>
        <div class="table-wrap" style="max-height:140px; overflow-y:auto; border:1px solid var(--line);">
          <table>
            <thead><tr><th>PROGRAM</th><th>SERVICE NOTES & PRESCRIPTIONS</th><th>DATE</th></tr></thead>
            <tbody id="prof-services-tbody"><tr><td colspan="3" class="muted">Loading...</td></tr></tbody>
          </table>
        </div>
      </div>

      <div class="modal-actions">
        <button type="button" class="button secondary" data-close="modal-resident-profile">Close</button>
      </div>
    </div>
  </div>

  <div class="toast" id="toast">Action completed successfully.</div>

</body>
</html>
