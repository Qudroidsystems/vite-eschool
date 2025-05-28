function showage(dob, elementId) {
    try {
        const element = document.getElementById(elementId);
        if (!element) {
            console.error(`Element #${elementId} not found for showage`);
            return;
        }
        if (!dob) {
            console.warn("No date of birth provided");
            element.value = '';
            return;
        }
        const dobDate = new Date(dob);
        if (isNaN(dobDate)) {
            console.warn("Invalid date of birth:", dob);
            element.value = '';
            return;
        }
        const today = new Date();
        let age = today.getFullYear() - dobDate.getFullYear();
        const monthDiff = today.getMonth() - dobDate.getMonth();
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dobDate.getDate())) {
            age--;
        }
        element.value = age;
        console.log(`Calculated age: ${age} for DOB: ${dob}`);
    } catch (error) {
        console.error("Error in showage:", error);
    }
}