// for future enhancements

// Show subcategory based on category
document.getElementById('category_select').addEventListener('change', function () {
    let selectedCat = this.value;
    let subSelect = document.getElementById('subcategory_select');

    // Reset the subcategory dropdown
    subSelect.value = "";

    // Loop through subcategory options
    [...subSelect.options].forEach(opt => {
        if (opt.value === "") {
            opt.hidden = false; // keep the placeholder shown
            return;
        }

        // Show only if data-cat matches selected category
        if (opt.dataset.cat === selectedCat) {
            opt.hidden = false;
        } else {
            opt.hidden = true;
        }
    });
});


// Show subcategory based on category
document.getElementById('shop_select').addEventListener('change', function () {
    let selectedCat = this.value;
    let subSelect = document.getElementById('category_select');

    // Reset the subcategory dropdown
    subSelect.value = "";

    // Loop through subcategory options
    [...subSelect.options].forEach(opt => {
        if (opt.value === "") {
            opt.hidden = false; // keep the placeholder shown
            return;
        }

        // Show only if data-cat matches selected category
        if (opt.dataset.cat === selectedCat) {
            opt.hidden = false;
        } else {
            opt.hidden = true;
        }
    });
});

