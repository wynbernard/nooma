const totalSale = document.getElementById("TotalSale_Bs_Ks_C");
const barSale = document.getElementById("barSale");
const corkage = document.getElementById("corkage");
const serviceCharge = document.getElementById("serviceCharge");
const kitchenSale = document.getElementById("kitchenSale");

function calculateKitchenSale() {
  const total = parseFloat(totalSale.value) || 0;
  const bar = parseFloat(barSale.value) || 0;
  const cork = parseFloat(corkage.value) || 0;
  const service = parseFloat(serviceCharge.value) || 0;

  const kitchen = total - bar - cork - service;

  kitchenSale.value = Math.max(0, kitchen).toFixed(2);
}

totalSale.addEventListener("input", calculateKitchenSale);
barSale.addEventListener("input", calculateKitchenSale);
corkage.addEventListener("input", calculateKitchenSale);
serviceCharge.addEventListener("input", calculateKitchenSale);

calculateKitchenSale();
