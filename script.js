console.log('script is working....');

const optionMenu = document.querySelector(".select-menu"),
    selectBtn = optionMenu.querySelector(".select-btn"),
    options = optionMenu.querySelectorAll(".options");
selectBtn.addEventListener("mouseenter", () => optionMenu.classList.add("active"));
options.forEach(option => {
    option.addEventListener("mouseleave", () => {
        optionMenu.classList.remove("active");
    });
});


// Load Data Grid

function dbload(tn) {
    const xhr = new XMLHttpRequest();
    xhr.open("POST", "process.php", true);
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

    xhr.onload = function () {
        if (xhr.status === 200) {
            console.log('Response received:', xhr.responseText);
            document.querySelector(".grid").innerHTML = xhr.responseText;
        } else {
            console.error('Error fetching data:', xhr.status);
        }
    };

    xhr.send("tn=" + encodeURIComponent(tn));
}

function closeModal() {
    document.getElementById("viewModal").style.display = "none";
    document.getElementById("modalFrame").src = "";
}


// For Delete Data

function dbdel(id, tn) {
    // Role-based access control for offers table
    if (tn === "offers") {
        // Check if user role is Manufacture (this should be passed from PHP or stored in session)
        // For now, we'll let the server-side handle the access control
        // The button visibility is already controlled in process.php
    }
    
    if (!confirm("Are you sure you want to delete this record?")) return;

    const xhr = new XMLHttpRequest();
    xhr.onload = function () {
        if (xhr.status === 200) {
            console.log('Delete request successful:', xhr.responseText);
            document.querySelector(".grid").innerHTML = xhr.responseText;
        } else {
            console.error('Delete request failed:', xhr.status);
        }
    }

    xhr.open("GET", "process.php?del=" + encodeURIComponent(id) + "&tn=" + encodeURIComponent(tn), true);
    xhr.send();
}


// For Update Data

function dbed(id, table) {
    // Role-based access control for offers table
    if (table === "offers") {
        // Check if user role is Manufacture (this should be passed from PHP or stored in session)
        // For now, we'll let the server-side handle the access control
        // The button visibility is already controlled in process.php
    }
    
    document.cookie = "edit_id=" + id;

    let url = "";
    if (table == "admin") {
        url = "Edit/edit_admin.php";
    } else if (table == "city") {
        url = "Edit/edit_city.php";
    } else if (table == "states") {
        url = "Edit/edit_states.php";
    } else if (table == "users") {
        url = "Edit/edit_users.php";
    } else if (table == "asm_attendance") {
        url = "Edit/edit_asm_attendance.php";
    } else if (table == "batch_distributor") {
        url = "Edit/edit_batch_distributor.php";
    } else if (table == "batch_retailer") {
        url = "Edit/edit_batch_retailer.php";
    } else if (table == "product_assigned_dist") {
        url = "Edit/edit_product_assigned_dist.php";
    } else if (table == "product_assigned_retailer") {
        url = "Edit/edit_product_assigned_retailer.php";
    } else if (table == "offers") {
        url = "Edit/edit_offers.php";
    } else if (table == "products") {
        url = "Edit/edit_products.php";
    } else if (table == "user_relations") {
        url = "Edit/edit_user_relations.php";
    } else if (table == "sold_products") {
        url = "Edit/edit_sold_products.php";
    } else if (table == "product_category") {
        url = "Edit/edit_product_category.php";
    } else if (table == "product_assignments_backup") {
        url = "Edit/edit_product_assignments_backup.php";
    } else if (table == "requested_products") {
        url = "Edit/edit_requested_products.php";
    } else {
        alert("Error occur !!");
    }

    window.location.href = url;
}


// For View Data

function dbview(id, table) {
    document.cookie = "view_id=" + id;

    const xhr = new XMLHttpRequest();
    xhr.onload = function () {
        document.getElementById("modalContent").innerHTML = xhr.responseText;
        document.getElementById("viewModal").style.display = "flex";
    };

    let url = "";
    if (table === "admin") {
        url = "View/view_admin.php";
    } else if (table === "city") {
        url = "View/view_city.php";
    } else if (table == "states") {
        url = "View/view_states.php";
    } else if (table == "users") {
        url = "View/view_users.php";
    } else if (table == "asm_attendance") {
        url = "View/view_asm_attendnce.php";
    } else if (table == "batch_distributor") {
        url = "View/view_batch_distributor.php";
    } else if (table == "batch_retailer") {
        url = "View/view_batch_retailer.php";
    } else if (table == "product_assigned_dist") {
        url = "View/view_product_assigned_dist.php";
    } else if (table == "product_assigned_retailer") {
        url = "View/view_product_assigned_retailer.php";
    } else if (table == "requested_products") {
        url = "View/view_requested_products.php";
    } else if (table == "sold_products") {
        url = "View/view_sold_products.php";
    } else if (table == "products") {
        url = "View/view_products.php";
    } else if (table == "product_category") {
        url = "View/view_product_category.php";
    } else if (table == "user_relations") {
        url = "View/view_user_relations.php";
    } else if (table == "offers") {
        url = "View/view_offers.php";
    } else if (table == "product_assignments_backup") {
        url = "View/view_product_assignments_backup.php";
    }

    if (url !== "") {
        xhr.open("GET", url, true);
        xhr.send();
    }
}


// For Insert Data

function dbinsert(table) {
    document.cookie = "insert_table=" + table;

    const xhr = new XMLHttpRequest();
    xhr.onload = function () {
        document.getElementById("modalContent").innerHTML = xhr.responseText;
        document.getElementById("viewModal").style.display = "flex";
    };

    let url = "";
    if (table === "admin") {
        url = "Insert/insert_admin.php";
    } else if (table === "city") {
        url = "Insert/insert_city.php";
    } else if (table == "states") {
        url = "Insert/insert_states.php";
    } else if (table == "users") {
        url = "Insert/insert_users.php";
    } else if (table == "asm_attendance") {
        url = "Insert/insert_asm_atttendance.php";
    } else if (table == "batch_distributor") {
        url = "Insert/insert_batch_distributor.php";
    } else if (table == "batch_retailer") {
        url = "Insert/insert_batch_retailer.php";
    } else if (table == "product_assigned_dist") {
        url = "Insert/insert_product_assigned_dist.php";
    } else if (table == "product_assigned_retailer") {
        url = "Insert/insert_product_assigned_retailer.php";
    } else if (table == "requested_products") {
        url = "Insert/insert_requested_products.php";
    } else if (table == "sold_products") {
        url = "Insert/insert_sold_products.php";
    } else if (table == "products") {
        url = "Insert/insert_products.php";
    } else if (table == "product_category") {
        url = "Insert/insert_product_category.php";
    } else if (table == "user_relations") {
        url = "Insert/insert_user_relations.php";
    } else if (table == "offers") {
        url = "Insert/insert_offers.php";
    } else if (table == "product_assignments_backup") {
        url = "Insert/insert_product_assignments_backup.php";
    }

    if (url !== "") {
        xhr.open("GET", url, true);
        xhr.send();
    }
}


// For ADD TO BATCH 

function addtobatch(role, table) {

    const xhr = new XMLHttpRequest();
    xhr.onload = function () {
        document.getElementById("modalContent").innerHTML = xhr.responseText;
        document.getElementById("viewModal").style.display = "flex";
    };

    let url = "Addbatch/add_to_batch_form.php?role="+ role +"&table="+ table;
    

    if (url !== "") {
        xhr.open("GET", url, true);
        xhr.send();
    }

}


// Report Download 

// For Manufacturer Report

function generateReport() {
    const xhr = new XMLHttpRequest();

    xhr.onload = function () {
        if (xhr.status === 200) {
            console.log('Response received:', xhr.responseText);
            document.querySelector(".grid").innerHTML = xhr.responseText;
        } else {
            console.error('Error fetching data:', xhr.status);
        }
    };
    xhr.open("GET", "Getreport/getreport.php", true);
    xhr.send();
}

function rload() {
    const dist = document.getElementById('dist').value;
    const s_date = document.getElementById('s_date').value;
    const e_date = document.getElementById('e_date').value;

    const query = `dist=${encodeURIComponent(dist)}&s_date=${encodeURIComponent(s_date)}&e_date=${encodeURIComponent(e_date)}`;

    const xhr = new XMLHttpRequest();
    xhr.onload = function () {
        if (xhr.status === 200) {
            document.getElementById("rg").innerHTML = xhr.responseText;
        } else {
            console.error('Error fetching data:', xhr.status);
        }
    };
    xhr.open("GET", "Getreport/processreport.php?" + query, true);
    xhr.send();
}

function dbloadr(batchId) {
    fetch("Getreport/viewreport.php?batch_id=" + batchId)
        .then(res => res.text())
        .then(data => {
            document.getElementById("rf").innerHTML = data;
        })
        .catch(err => {
            console.error("Error loading sold products:", err);
        });
}


// For Distributor Report

function generateReportdis() {
    const xhr = new XMLHttpRequest();
    xhr.onload = function () {
        if (xhr.status === 200) {
            console.log('Response received:', xhr.responseText);

            // Try loading into .grid, else fallback to #table-container
            const container = document.querySelector(".grid") || document.querySelector("#table-container");
            if (container) {
                container.innerHTML = xhr.responseText;
            } else {
                console.error("Target container not found.");
            }
        } else {
            console.error('Error fetching report:', xhr.status);
        }
    };
    xhr.open("GET", "GetReport/getreport_dis.php", true);
    xhr.send();
}


function rloaddis() {
    const ret = document.getElementById('ret').value;
    const s_date = document.getElementById('s_date').value;
    const e_date = document.getElementById('e_date').value;

    const params = new URLSearchParams({
        ret: ret,
        s_date: s_date,
        e_date: e_date
    });

    const xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            if (xhr.status === 200) {
                document.getElementById("rg").innerHTML = xhr.responseText;
            } else {
                console.error("Error fetching report:", xhr.status);
            }
        }
    };

    xhr.open("GET", "GetReport/processreport_dis.php?" + params.toString(), true);
    xhr.send();
}

function dbloadrdis(batchId) {
    fetch("GetReport/viewreport_dis.php?batch_id=" + batchId)
        .then(res => res.text())
        .then(data => {
            document.getElementById("rf").innerHTML = data;
        })
        .catch(err => {
            console.error("Error loading sold products:", err);
        });
}

// Retailer product request (from products table)
window.requestProduct = function requestProduct(productId, productName) {
    if (!confirm(`Request product: ${productName}?`)) {
        return;
    }
    const params = new URLSearchParams();
    params.append('product_id', productId);
    fetch('request_product.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: params.toString()
    })
    .then(res => res.json())
    .then(data => {
        if (data && data.success) {
            alert('Request sent successfully');
            try { dbload('requested_products'); } catch (e) {}
        } else {
            alert((data && data.error) || 'Request failed');
        }
    })
    .catch(err => {
        console.error(err);
        alert('Network error while sending request');
    });
}

// Retailer: mark a product as sold in market
window.markAsRetailed = function markAsRetailed(productId, productName, skuId, sourceType = '', sourceId = 0) {
    const priceStr = prompt(`Enter selling price for: ${productName}`, '0');
    if (priceStr === null) return;
    const price = parseFloat(priceStr);
    if (isNaN(price) || price < 0) {
        alert('Invalid price');
        return;
    }
    const params = new URLSearchParams({ product_id: productId, product_name: productName, sku_id: skuId || '', price: price, source_type: sourceType || '', source_id: sourceId || 0 });
    fetch('retailed_product_mark.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: params.toString()
    })
    .then(res => res.json())
    .then(data => {
        if (data && data.success) {
            alert('Marked as sold successfully');
            // Refresh stock and retailed tables if visible
            try { dbload('stock'); } catch (e) {}
            setTimeout(() => { try { dbload('retailed_product'); } catch (e) {} }, 300);
        } else {
            alert((data && data.error) || 'Operation failed');
        }
    })
    .catch(err => {
        console.error(err);
        alert('Network error while marking as sold');
    });
}

// Retailer: revert a retailed product back into stock
window.revertRetailed = function revertRetailed(retailedId, productId, productName, sourceType = '', sourceId = 0) {
    if (!confirm(`Revert sale for: ${productName}?`)) return;
    const params = new URLSearchParams({ id: retailedId });
    fetch('revert_retailed_product.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: params.toString()
    })
    .then(res => res.json())
    .then(data => {
        if (data && data.success) {
            alert('Reverted successfully');
            try { dbload('stock'); } catch (e) {}
            setTimeout(() => { try { dbload('retailed_product'); } catch (e) {} }, 300);
        } else {
            alert((data && data.error) || 'Revert failed');
        }
    })
    .catch(err => {
        console.error(err);
        alert('Network error while reverting');
    });
}

// Distributor: Check stock and forward request if unavailable
window.checkAndForwardRequest = function checkAndForwardRequest(requestId) {
    if (!confirm('Check distributor stock and forward to manufacturer if unavailable?')) {
        return;
    }
    const params = new URLSearchParams();
    params.append('request_id', requestId);
    fetch('handle_request_availability.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: params.toString()
    })
    .then(res => res.json())
    .then(data => {
        if (data && data.success) {
            alert(data.message || 'Processed');
            try { dbload('requested_products'); } catch (e) {}
        } else {
            alert((data && data.error) || 'Operation failed');
        }
    })
    .catch(err => {
        console.error(err);
        alert('Network error');
    });
}

// Distributor: directly approve and notify retailer when stock is available
window.sendToRetailer = function sendToRetailer(requestId) {
    if (!confirm('Send this product to the retailer and approve the request?')) {
        return;
    }
    const params = new URLSearchParams();
    params.append('request_id', requestId);
    fetch('send_request_retailer.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: params.toString()
    })
    .then(res => res.json())
    .then(data => {
        if (data && data.success) {
            alert(data.message || 'Sent to retailer');
            try { dbload('requested_products'); } catch (e) {}
        } else {
            alert((data && data.error) || 'Operation failed');
        }
    })
    .catch(err => {
        console.error(err);
        alert('Network error');
    });
}

// Manufacturer: Approve forwarded distributor requests
window.approveRequestByManufacturer = function approveRequestByManufacturer(requestId) {
    if (!confirm('Approve this distributor request?')) {
        return;
    }
    const params = new URLSearchParams();
    params.append('request_id', requestId);
    fetch('approve_request_manufacturer.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: params.toString()
    })
    .then(res => res.json())
    .then(data => {
        if (data && data.success) {
            alert(data.message || 'Approved');
            try { dbload('requested_products'); } catch (e) {}
        } else {
            alert((data && data.error) || 'Approval failed');
        }
    })
    .catch(err => {
        console.error(err);
        alert('Network error');
    });
}