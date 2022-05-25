"use strict";

function initCheckboxes() {
	const checkboxData = new Set(JSON.parse(localStorage.getItem("checkboxState")));

	for(const checkedId of checkboxData) {
		(document.getElementById(checkedId) || {}).checked = "checked";
	}
}

function checkboxUpdate(checkbox) {
	const checkboxData = new Set(JSON.parse(localStorage.getItem("checkboxState")));

	if(checkbox.checked) {
		checkboxData.add(checkbox.id);
	} else {
		checkboxData.delete(checkbox.id);
	}

	localStorage.setItem("checkboxState", JSON.stringify(Array.from(checkboxData)));
}

window.addEventListener("DOMContentLoaded", initCheckboxes);
