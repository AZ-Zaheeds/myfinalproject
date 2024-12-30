// Show the staff selection container
function showStaffContainer(event) {
    event.preventDefault(); // Prevent page reload
    const staffContainer = document.getElementById('staff-container');
    const studentContainer = document.getElementById('student-container');

    // Hide the student container if it's visible
    if (studentContainer.style.display === 'block') {
        studentContainer.style.display = 'none';
    }

    // Show the staff container
    staffContainer.style.display = 'block';
}

// Show the student selection container
function showStudentContainer(event) {
    event.preventDefault(); // Prevent page reload
    const staffContainer = document.getElementById('staff-container');
    const studentContainer = document.getElementById('student-container');

    // Hide the staff container if it's visible
    if (staffContainer.style.display === 'block') {
        staffContainer.style.display = 'none';
    }

    // Show the student container
    studentContainer.style.display = 'block';
}

// Hide the containers when clicking outside
window.onclick = function(event) {
    const staffContainer = document.getElementById('staff-container');
    const studentContainer = document.getElementById('student-container');

    // Check if the click is outside both containers and not on their triggers
    if (
        !staffContainer.contains(event.target) &&
        !studentContainer.contains(event.target) &&
        event.target.id !== 'sl1' && // Staff Login link ID
        event.target.id !== 'sl2'    // Student Login link ID
    ) {
        staffContainer.style.display = 'none';
        studentContainer.style.display = 'none';
    }
};
