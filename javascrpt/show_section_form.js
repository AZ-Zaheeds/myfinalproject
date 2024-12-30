 // Function to show specific sections
 function showSection(sectionId) {
    var sections = document.querySelectorAll('form, table, .edit-form');
    sections.forEach(function(section) {
        section.style.display = 'none'; // Hide all sections
    });

    var section = document.getElementById(sectionId);
    if(section){
        section.style.display = 'block'; // Show the selected section
    }
}

// Function to show the Edit Form
function showEditForm(coordinatorId, currentLevel) {
    // Hide all forms and tables
    var sections = document.querySelectorAll('form, table, .edit-form');
    sections.forEach(function(section) {
        section.style.display = 'none';
    });

    // Fill the form with current data
    document.getElementById('edit_coordinator_id').value = coordinatorId;
    document.getElementById('new_level').value = currentLevel;

    // Show the edit form
    document.getElementById('editForm').style.display = 'flex';
}

// Function to cancel the Edit Form
function cancelEdit() {
    document.getElementById('editForm').style.display = 'none';
}