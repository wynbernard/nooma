function formatAmount(value) {
  value = value.replace(/,/g, "").replace(/[^\d.]/g, "");

  if (!value) {
    return "";
  }

  let number = parseFloat(value);

  if (isNaN(number)) {
    return "";
  }

  return number.toLocaleString("en-PH", {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });
}

function getNumericAmount(value) {
  return parseFloat(value.replace(/,/g, "")) || 0;
}

function calculateOwnerTotal() {
  let total = 0;

  document.querySelectorAll(".owner-amount").forEach((input) => {
    total += getNumericAmount(input.value);
  });

  document.getElementById("totalOwnerAmount").value = total.toLocaleString(
    "en-PH",
    {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    },
  );

  document.getElementById("ownerCount").value =
    document.querySelectorAll(".owner-row").length;
}

function attachAmountListener() {
  document.querySelectorAll(".owner-amount").forEach((input) => {
    if (input.dataset.listener === "true") {
      return;
    }

    input.dataset.listener = "true";

    input.addEventListener("input", function () {
      this.value = formatAmount(this.value);

      calculateOwnerTotal();
    });
  });
}

function addOwner() {
  const ownerList = document.getElementById("ownerList");

  const row = document.createElement("div");

  row.className = "owner-row grid md:grid-cols-3 gap-4 mb-4";

  row.innerHTML = `

        <div>

            <label class="block text-sm font-semibold text-gray-700 mb-2">
                Owner Name
            </label>

            <input
                type="text"
                name="owner_name[]"
                placeholder="Enter owner name"
                class="w-full border border-gray-300 rounded-xl px-4 py-3
                       focus:outline-none focus:ring-2 focus:ring-blue-500"
                required
            >

        </div>


        <div>

            <label class="block text-sm font-semibold text-gray-700 mb-2">
                Amount
            </label>

            <input
                type="text"
                name="owner_amount[]"
                placeholder="0.00"
                inputmode="decimal"
                class="owner-amount w-full border border-gray-300 rounded-xl px-4 py-3
                       focus:outline-none focus:ring-2 focus:ring-blue-500"
                required
            >

        </div>


        <div class="flex items-end">

            <button
                type="button"
                onclick="removeOwner(this)"
                class="w-full md:w-auto px-5 py-3 rounded-xl
                       bg-red-500 text-white
                       hover:bg-red-600 transition"
            >
                Remove
            </button>

        </div>

    `;

  ownerList.appendChild(row);

  attachAmountListener();

  calculateOwnerTotal();
}

function removeOwner(button) {
  const rows = document.querySelectorAll(".owner-row");

  // Don't allow all owners to be removed
  if (rows.length <= 1) {
    alert("At least one owner is required.");

    return;
  }

  button.closest(".owner-row").remove();

  calculateOwnerTotal();
}

function initOwnerAccounts() {
  attachAmountListener();
  calculateOwnerTotal();
}

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", initOwnerAccounts);
} else {
  initOwnerAccounts();
}
