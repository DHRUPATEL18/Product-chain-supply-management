function fillp(val) {
    const xhr = new XMLHttpRequest();
    xhr.onload = function () {
        if (xhr.status === 200) {
            document.getElementById("parent_id").innerHTML = xhr.responseText;
        }
    };
    xhr.open("GET", "Insert/insert_user_relations.php?fill=parent&type=" + encodeURIComponent(val), true);
    xhr.send();
}

function fillc(val) {
    const xhr = new XMLHttpRequest();
    xhr.onload = function () {
        if (xhr.status === 200) {
            document.getElementById("child_id").innerHTML = xhr.responseText;
        }
    };
    xhr.open("GET", "Insert/insert_user_relations.php?fill=child&type=" + encodeURIComponent(val), true);
    xhr.send();
}

function fillcity(stateId) {
    const xhr = new XMLHttpRequest();
    xhr.onload = function () {
        if (xhr.status === 200) {
            document.getElementById("city_id").innerHTML = xhr.responseText;
        }
    };
    xhr.open("GET", "Insert/insert_users.php?state_id=" + encodeURIComponent(stateId), true);
    xhr.send();
}

// Add globally available loadCities for user insert form
function loadCities(stateId) {
    if (!stateId) {
        document.getElementById("city_id").innerHTML = '<option value="">-- Select City --</option>';
        return;
    }
    fetch("Insert/insert_users.php?fill=city&state_id=" + stateId)
        .then(response => response.text())
        .then(data => {
            document.getElementById("city_id").innerHTML = data;
        });
}