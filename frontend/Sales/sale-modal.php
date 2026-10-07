        <!-- SALES INPUT MODAL -->
        <div id="saleModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-gray-950/60 p-2 backdrop-blur-sm sm:p-4 md:p-6">
            <div class="min-h-full flex items-center justify-center">
                <div class="flex h-[calc(100vh-1rem)] w-full max-w-7xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-black/10 sm:h-[calc(100vh-2rem)] md:h-[calc(100vh-3rem)]">
                    <div class="sticky top-0 z-20 flex shrink-0 items-center justify-between gap-4 border-b border-gray-200 bg-white/95 px-4 py-4 backdrop-blur sm:px-6 md:px-8">
                        <div class="flex items-center gap-3">
                            <div>
                                <p class="mb-1 text-[10px] font-bold uppercase tracking-widest text-blue-600">Sales Management</p>
                                <h3 id="saleModalTitle" class="text-xl font-bold text-gray-900">Create New Sale</h3>
                                <p class="mt-1 text-xs text-gray-500 sm:text-sm">Enter the sale, payment, and reconciliation details.</p>
                            </div>
                            <span id="shortOverStatus" class="hidden inline-flex items-center rounded-full border px-2.5 py-1 text-[11px] font-semibold tracking-wide">
                                Balanced
                            </span>
                        </div>
                        <button type="button" id="closeSaleModal" aria-label="Close sale form"
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg text-gray-500 transition hover:bg-gray-100 hover:text-gray-900 focus:outline-none focus:ring-4 focus:ring-blue-500/10">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 6l12 12M18 6 6 18"></path>
                            </svg>
                        </button>
                    </div>

                    <div class="min-h-0 flex-1 overflow-y-auto p-4 sm:p-6 md:p-8">

                <form id="salesForm" class="[&_label]:text-gray-700 [&_input:not([type=hidden]):not([type=file])]:focus:border-blue-500 [&_input:not([type=hidden]):not([type=file])]:focus:outline-none [&_input:not([type=hidden]):not([type=file])]:focus:ring-4 [&_input:not([type=hidden]):not([type=file])]:focus:ring-blue-500/10 [&_select]:focus:border-blue-500 [&_select]:focus:outline-none [&_select]:focus:ring-4 [&_select]:focus:ring-blue-500/10">

                        <!-- TRANSACTION & DINING DETAILS -->
                        <!-- 1st row of the form -->
                            <div class="mb-4 flex items-center gap-3">
                                <span class="h-5 w-1 rounded-full bg-blue-600"></span>
                                <h4 class="text-sm font-bold text-gray-900">Transaction Details</h4>
                                <span class="h-px flex-1 bg-gray-200"></span>
                            </div>
                            <div class="grid md:grid-cols-4 gap-4 mb-6">

                                <div>
                                    <label for="saleDate" class="block text-sm font-medium mb-2">
                                        Sale Date
                                    </label>
                                    <input type="date"
                                        id="saleDate"
                                        required
                                        class="w-full border rounded-xl px-4 py-3">
                                </div>

                                <div>
                                    <label for="pax" class="block text-sm font-medium mb-2">
                                        Number of Pax
                                    </label>
                                    <input type="number"
                                        id="pax"
                                        placeholder="e.g., 0"
                                        required
                                        class="w-full border rounded-xl px-4 py-3">
                                </div>

                                <div>
                                    <label for="tableNumber" class="block text-sm font-medium mb-2">
                                        Table Number
                                    </label>
                                    <input type="text"
                                        id="tableNumber"
                                        placeholder="e.g., Table 4"
                                        required
                                        class="w-full border rounded-xl px-4 py-3">
                                </div>

                                <div>
                                    <label for="saleType" class="block text-sm font-medium mb-2">
                                        Sale Type
                                    </label>
                                    <select id="saleType"
                                            name="sale_type"
                                            required
                                            class="w-full border rounded-xl px-4 py-3 bg-white">
                                        <option value="" disabled selected>Select sale type</option>
                                        <option value="Lunch">Lunch Sale</option>
                                        <option value="Closing">Closing Sale</option>
                                    </select>
                                    <p id="saleDuplicateError" class="hidden mt-2 text-sm text-red-600">
                                        A sale with this date and sale type already exists.
                                    </p>
                                </div>

                                <div>
                                    <label for="saleChannel" class="block text-sm font-medium mb-2">
                                        Sales Channel
                                    </label>
                                    <select id="saleChannel"
                                            name="sale_channel"
                                            class="w-full border rounded-xl px-4 py-3 bg-white">
                                        <option value="POS" selected>POS</option>
                                        <option value="Non POS">Non POS</option>
                                    </select>
                                </div>

                            </div>

                            <!-- 2nd row of the form -->
                            <div class="grid md:grid-cols-4 gap-4 mb-6">

                                <div>
                                    <label for="kitchenSale" class="block text-sm font-medium mb-2">
                                        Kitchen Sale
                                    </label>
                                    <input type="number"
                                        id="kitchenSale"
                                        value="0"
                                        step="0.01"
                                       
                                        class="js-nonpos-total-input w-full border rounded-xl px-4 py-3 bg-gray-100">
                                </div>
                                <div>
                                    <label for="barSale" class="block text-sm font-medium mb-2">
                                        Bar Sale
                                    </label>
                                    <input type="number"
                                        id="barSale"
                                        step="0.01"
                                       
                                        required
                                        class="js-nonpos-total-input w-full border rounded-xl px-4 py-3">
                                </div>

                                <div id="giftCheckSaleContainer" class="hidden">
                                    <label for="giftCheckSale" class="block text-sm font-medium mb-2">
                                        Gift Check Sale
                                    </label>
                                    <input type="text"
                                        id="giftCheckSale"
                                        name="gift_check_sale"
                                        placeholder="0.00"
                                        inputmode="decimal"
                                        class="js-nonpos-total-input w-full border rounded-xl px-4 py-3 bg-white">
                                </div>

                                <div id="otherProductsContainer" class="hidden">
                                    <label for="otherProducts" class="block text-sm font-medium mb-2">
                                        Other Products
                                    </label>
                                    <input type="text"
                                        id="otherProducts"
                                        name="other_products"
                                        placeholder="0.00"
                                        inputmode="decimal"
                                        class="js-nonpos-total-input w-full border rounded-xl px-4 py-3 bg-white">
                                </div>

                                <div>
                                    <label for="corkage" class="block text-sm font-medium mb-2">
                                        Corkage
                                    </label>
                                    <input type="number"
                                        id="corkage"
                                        step="0.01"
                                       
                                        class="js-nonpos-total-input w-full border rounded-xl px-4 py-3">
                                </div>

                                <div>
                                <label for="totalSale" class="block text-sm font-medium mb-2">
                                        Total Sale (Ks + Bs + Corkage)
                                    </label>

                                    <input type="number"
                                        id="totalSale"
                                        readonly
                                        required
                                        class="w-full border rounded-xl px-4 py-3 bg-gray-100">
                                </div>
                            </div>
                            <!-- 3rd row of the form -->

                            <div class="grid md:grid-cols-3 gap-4 mb-6">
                                <div>
                                    <label for="serviceCharge" class="block text-sm font-medium mb-2">
                                        Service Charge
                                    </label>
                                    <input type="number"
                                        id="serviceCharge"
                                        step="0.01"
                                            
                                        class="w-full border rounded-xl px-4 py-3">
                                </div>

                                <div>
                                    <label for="grandTotal" class="block text-sm font-medium mb-2">
                                        Declare Grand Total (â‚±)
                                    </label>
                                    <input type="number"
                                        id="grandTotal"
                                        step="0.01"
                                       
                                        required
                                        class="w-full border rounded-xl px-4 py-3">
                                </div>

                            </div>
                            <!-- 4rth row of the form -->
                                                    <div class="flex items-center gap-3 mb-4 pt-5">
                                                        <span class="h-5 w-1 rounded-full bg-blue-600"></span>
                                                        <h4 class="text-sm font-bold text-gray-900">Mode of Payment</h4>
                                                        <span class="h-px flex-1 bg-gray-200"></span>
                                                </div>
                                <div class="grid md:grid-cols-4 gap-4 mb-6">

                                    <div>
                                            <label for="cashRemitted" class="block text-sm font-medium mb-2">
                                                Cash Remitted 
                                            </label>
                                            <input type="number"
                                                id="cashRemitted"
                                                step="0.01"
                                               
                                                placeholder="0.00"
                                                class="w-full border rounded-xl px-4 py-3">
                                        </div>

                                        <div>
                                            <label for="gcashQrph" class="block text-sm font-medium mb-2">
                                                Gcash + Qrph
                                            </label>
                                            <input type="number"
                                                id="gcashQrph"
                                                step="0.01"
                                               
                                            placeholder="0.00"
                                                class="w-full border rounded-xl px-4 py-3">
                                        </div>

                                        <div>
                                            <label for="paymaya" class="block text-sm font-medium mb-2">
                                                Paymaya
                                            </label>
                                            <input type="number"
                                                id="paymaya"
                                                step="0.01"
                                               
                                                placeholder="0.00"
                                                class="w-full border rounded-xl px-4 py-3">
                                        </div>

                                        <div>
                                            <label for="amex" class="block text-sm font-medium mb-2">
                                                Amex
                                            </label>
                                            <input type="number"
                                                id="amex"
                                                step="0.01"
                                               
                                                placeholder="0.00"
                                                class="w-full border rounded-xl px-4 py-3">
                                        </div>

                                    </div>
                                    <div class="grid md:grid-cols-4 gap-4 mb-6">

                                    <div>
                                            <label for="visa" class="block text-sm font-medium mb-2">
                                                Visa 
                                            </label>
                                            <input type="number"
                                                id="visa"
                                                step="0.01"
                                               
                                                placeholder="0.00"
                                                class="w-full border rounded-xl px-4 py-3">
                                        </div>

                                        <div>
                                            <label for="mastercard" class="block text-sm font-medium mb-2">
                                                Mastercard
                                            </label>
                                            <input type="number"
                                                id="mastercard"
                                                step="0.01"
                                               
                                                placeholder="0.00"
                                                class="w-full border rounded-xl px-4 py-3">
                                        </div>

                                        <div>
                                            <label for="bancnet" class="block text-sm font-medium mb-2">
                                                Bancnet
                                            </label>
                                            <input type="number"
                                                id="bancnet"
                                                step="0.01"
                                               
                                                placeholder="0.00"
                                                class="w-full border rounded-xl px-4 py-3">
                                        </div>

                                        <div>
                                            <label for="jcb" class="block text-sm font-medium mb-2">
                                                JCB
                                            </label>
                                            <input type="number"
                                                id="jcb"
                                                step="0.01"
                                               
                                                placeholder="0.00"
                                                class="w-full border rounded-xl px-4 py-3">
                                        </div>

                                    </div>
                                    <div class="grid md:grid-cols-4 gap-4 mb-6">

                                    <div>
                                            <label for="bpi" class="block text-sm font-medium mb-2">
                                                Direct BT BPI Nooma 
                                            </label>
                                            <input type="number"
                                                id="bpi"
                                                step="0.01"
                                               
                                                placeholder="0.00"
                                                class="w-full border rounded-xl px-4 py-3">
                                        </div>

                                        <div>
                                            <label for="easwest" class="block text-sm font-medium mb-2">
                                                Direct BT EASWEST Nooma
                                            </label>
                                            <input type="number"
                                                id="easwest"
                                                step="0.01"
                                               
                                                placeholder="0.00"
                                                class="w-full border rounded-xl px-4 py-3">
                                        </div>

                                        <div>
                                            <label for="giftcheck" class="block text-sm font-medium mb-2">
                                                Nooma Gift Check
                                            </label>
                                            <input type="number"
                                                id="giftcheck"
                                                step="0.01"
                                               
                                                placeholder="0.00"
                                                class="w-full border rounded-xl px-4 py-3">
                                        </div>

                                        <div>
                                            <label for="cheque" class="block text-sm font-medium mb-2">
                                                Cheque Payment
                                            </label>
                                            <input type="number"
                                                id="cheque"
                                                step="0.01"
                                               
                                                placeholder="0.00"
                                                class="w-full border rounded-xl px-4 py-3">
                                        </div>

                                    </div>
                                        <!-- Total Payment -->
                                        <div class="flex justify-end mb-6">
                                            <div class="w-full md:w-1/3">
                                                <label
                                                    for="totalPayment"
                                                    class="block text-sm font-semibold mb-2"
                                                >
                                                    Total
                                                </label>

                                                <input
                                                    type="text"
                                                    id="totalPayment"
                                                    name="total_payment"
                                                    placeholder="0.00"
                                                    readonly
                                                    class="w-full border-2 border-blue-500 rounded-xl px-4 py-3 bg-blue-50 text-blue-700 font-bold text-lg"
                                                >
                                            </div>
                                        </div>
                                        <!-- 4rth row of the form -->
                                        <!-- Owners Accounts -->
                                        <div class="flex items-center gap-3 mb-4 pt-5">
                                            <span class="h-5 w-1 rounded-full bg-blue-600"></span>
                                            <h4 class="text-sm font-bold text-gray-900">Owner Accounts</h4>
                                            <span class="h-px flex-1 bg-gray-200"></span>
                                        </div>

                                        <?php include '../../backend/ownerAccount/GetOwnerAccount.php'; ?>
                                        <div class="grid md:grid-cols-4 gap-4 mb-6">
                                            <?php if (!empty($owner_accounts)): ?>
                                                <?php foreach ($owner_accounts as $account): ?>
                                                    <div>
                                                        <label
                                                            for="owner_<?= $account['account_holder_id'] ?>"
                                                            class="block text-sm font-medium mb-2"
                                                        >
                                                            <?= htmlspecialchars($account['account_name']) ?>
                                                        </label>
                                                        <input
                                                            type="text"
                                                            id="owner_<?= $account['account_holder_id'] ?>"
                                                            name="owner_account[<?= $account['account_holder_id'] ?>]"
                                                            placeholder="0.00"
                                                            step="0.01"
                                                           
                                                            inputmode="decimal"
                                                            class="owner-account w-full border rounded-xl px-4 py-3 mb-2"
                                                        >
                                                        <input
                                                            type="hidden"
                                                            id="ownerImage_<?= $account['account_holder_id'] ?>"
                                                            value=""
                                                        >
                                                        <div class="owner-image-field relative" data-account-id="<?= $account['account_holder_id'] ?>">
                                                            <input
                                                                type="file"
                                                                id="ownerImageFile_<?= $account['account_holder_id'] ?>"
                                                                name="owner_image_<?= $account['account_holder_id'] ?>"
                                                                accept=".jpg,.jpeg,.png,.gif,.webp"
                                                                class="owner-image-file hidden"
                                                            >
                                                            <button
                                                                type="button"
                                                                class="pick-owner-image flex items-center justify-between gap-2 w-full border rounded-xl px-3 py-2 text-sm bg-gray-50 hover:bg-gray-100"
                                                            >
                                                                <span class="owner-image-label text-gray-500 truncate">Attach image</span>
                                                            </button>
                                                            <div class="owner-image-preview mt-2 hidden items-center gap-2">
                                                                <img src="" alt="Owner account attachment" class="h-16 w-16 object-cover rounded-lg border">
                                                                <button type="button" class="clear-owner-image text-xs font-medium text-red-600 hover:text-red-700">
                                                                    Remove
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <div class="md:col-span-4">
                                                    <p class="text-sm text-gray-500 bg-gray-50 border rounded-xl p-4">
                                                        No active owner accounts found.
                                                    </p>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <div id="unpaidAccountsContainer" class="space-y-3 mb-3 p-4 bg-red-50 border border-red-200 rounded-xl">
                                            <div class="grid md:grid-cols-3 gap-4 unpaid-account-row" data-index="0">
                                                <div>
                                                    <label for="unpaidAccountName" class="block text-sm font-medium mb-2">
                                                        Unpaid Account Name
                                                    </label>
                                                    <input
                                                        type="text"
                                                        id="unpaidAccountName"
                                                        name="unpaid_account_name"
                                                        placeholder="e.g., Juan Dela Cruz"
                                                        class="w-full border rounded-xl px-4 py-3 bg-white"
                                                    >
                                                </div>
                                                <div>
                                                    <label for="unpaidAccountAmount" class="block text-sm font-medium mb-2">
                                                        Unpaid Account Amount
                                                    </label>
                                                    <div class="flex gap-2">
                                                        <input
                                                            type="text"
                                                            id="unpaidAccountAmount"
                                                            name="unpaid_account_amount"
                                                            placeholder="0.00"
                                                            inputmode="decimal"
                                                            class="w-full border rounded-xl px-4 py-3 bg-white"
                                                        >
                                                    </div>
                                                </div>
                                                <div>
                                                    <label for="unpaidAccountNote" class="block text-sm font-medium mb-2">
                                                        Notes
                                                    </label>
                                                    <input
                                                        type="text"
                                                        id="unpaidAccountNote"
                                                        name="unpaid_account_note"
                                                        placeholder="Add notes..."
                                                        class="w-full border rounded-xl px-4 py-3 bg-white"
                                                    >
                                                </div>
                                            </div>
                                        </div>
                                        <button type="button" id="addUnpaidAccount" class="mb-6 px-4 py-2 bg-red-100 text-red-700 rounded-xl font-semibold hover:bg-red-200">
                                            Add Unpaid Account
                                        </button>

                                        <!-- TOTAL OWNER ACCOUNTS -->
                                        <div class="flex justify-end mb-6">
                                            <div class="w-full md:w-1/3">
                                                <label
                                                    for="totalOwnerAccounts"
                                                    class="block text-sm font-semibold mb-2"
                                                >
                                                    Total Owners Accounts
                                                </label>

                                                <input
                                                    type="text"
                                                    id="totalOwnerAccounts"
                                                    name="total_owner_accounts"
                                                    value="0.00"
                                                    readonly
                                                    class="w-full border-2 border-blue-500 rounded-xl px-4 py-3 bg-blue-50 text-blue-700 font-bold text-lg"
                                                >
                                            </div>
                                        </div>
                                        <!-- OTHER PAYMENTS -->
                                        <div class="flex items-center gap-3 mb-4 pt-5">
                                            <span class="h-5 w-1 rounded-full bg-blue-600"></span>
                                            <h4 class="text-sm font-bold text-gray-900">Other Payments</h4>
                                            <span class="h-px flex-1 bg-gray-200"></span>
                                        </div>

                                        <div class="grid md:grid-cols-3 gap-4 mb-6">

                                            <!-- CASH -->
                                            <div>
                                                <label for="otherCash" class="block text-sm font-medium mb-2">
                                                    Cash
                                                </label>
                                                <input
                                                    type="number"
                                                    id="otherCash"
                                                    name="other_cash"
                                                    step="0.01"
                                                   
                                                    placeholder="0.00"
                                                    class="other-payment w-full border rounded-xl px-4 py-3"
                                                >
                                            </div>

                                            <!-- CARD (MAYA TERMINAL) -->
                                            <div>
                                                <label for="otherMayaTerminal" class="block text-sm font-medium mb-2">
                                                    Card (Maya Terminal)
                                                </label>
                                                <input
                                                    type="number"
                                                    id="otherMayaTerminal"
                                                    name="other_maya_terminal"
                                                    step="0.01"
                                                   
                                                    placeholder="0.00"
                                                    class="other-payment w-full border rounded-xl px-4 py-3"
                                                >
                                            </div>

                                            <!-- DIRECT BT BPI NOOMA -->
                                            <div>
                                                <label for="otherBpiNooma" class="block text-sm font-medium mb-2">
                                                    Direct BT BPI Nooma
                                                </label>
                                                <input
                                                    type="number"
                                                    id="otherBpiNooma"
                                                    name="other_bpi_nooma"
                                                    step="0.01"
                                                   
                                                    placeholder="0.00"
                                                    class="other-payment w-full border rounded-xl px-4 py-3"
                                                >
                                            </div>

                                            <!-- DIRECT BT EASTWEST NOOMA -->
                                            <div>
                                                <label for="otherEastwestNooma" class="block text-sm font-medium mb-2">
                                                    Direct BT EastWest Nooma
                                                </label>
                                                <input
                                                    type="number"
                                                    id="otherEastwestNooma"
                                                    name="other_eastwest_nooma"
                                                    step="0.01"
                                                   
                                                    placeholder="0.00"
                                                    class="other-payment w-full border rounded-xl px-4 py-3"
                                                >
                                            </div>

                                            <!-- GIFT CHECK -->
                                            <div>
                                                <label for="otherGiftCheck" class="block text-sm font-medium mb-2">
                                                    Gift Check
                                                </label>
                                                <input
                                                    type="number"
                                                    id="otherGiftCheck"
                                                    name="other_gift_check"
                                                    step="0.01"
                                                   
                                                    placeholder="0.00"
                                                    class="other-payment w-full border rounded-xl px-4 py-3"
                                                >
                                            </div>

                                            <!-- CHEQUES -->
                                            <div>
                                                <label for="otherCheques" class="block text-sm font-medium mb-2">
                                                    Cheques
                                                </label>
                                                <input
                                                    type="number"
                                                    id="otherCheques"
                                                    name="other_cheques"
                                                    step="0.01"
                                                   
                                                    placeholder="0.00"
                                                    class="other-payment w-full border rounded-xl px-4 py-3"
                                                >
                                            </div>

                                        </div>

                                        <!-- TOTAL OTHER PAYMENTS -->
                                        <div class="flex justify-end mb-6">
                                            <div class="w-full md:w-1/3">

                                                <label
                                                    for="totalOtherPayments"
                                                    class="block text-sm font-semibold mb-2"
                                                >
                                                    Total Other Payments
                                                </label>

                                                <input
                                                    type="text"
                                                    id="totalOtherPayments"
                                                    name="total_other_payments"
                                                    value="0.00"
                                                    readonly
                                                    class="w-full border-2 border-blue-500 rounded-xl px-4 py-3 bg-blue-50 text-blue-700 font-bold text-lg"
                                                >

                                            </div>
                                        </div>
                                        <!-- MARKETING EXPENSES F & B -->
                                        <div class="flex items-center gap-3 mb-4 pt-5">
                                            <span class="h-5 w-1 rounded-full bg-blue-600"></span>
                                            <h4 class="text-sm font-bold text-gray-900">Marketing Expenses (F&amp;B)</h4>
                                            <span class="h-px flex-1 bg-gray-200"></span>
                                        </div>
                                        <!-- MARKETING EXPENSES F&B -->
                                        <div class="grid md:grid-cols-3 gap-4 mb-6">
                                            <!-- Marketing Expenses F&B -->
                                            <div>
                                                <label for="marketingExpensesF" class="block text-sm font-medium mb-2">
                                                    Marketing Expenses F&B
                                                </label>
                                                <input
                                                    type="text"
                                                    id="marketingExpensesF"
                                                    name="marketing_expenses_f"
                                                    placeholder="0.00"
                                                    step="0.01"
                                                   
                                                    inputmode="decimal"
                                                    class="marketing-expense w-full border rounded-xl px-4 py-3"
                                                >
                                            </div>
                                            <!-- DJ Ronald -->
                                            <div>
                                                <label for="djRonald" class="block text-sm font-medium mb-2">
                                                    DJ Ronald F&B
                                                </label>
                                                <input
                                                    type="text"
                                                    id="djRonald"
                                                    name="dj_ronald_fb"
                                                    placeholder="0.00"
                                                    step="0.01"
                                                   
                                                    inputmode="decimal"
                                                    class="marketing-expense w-full border rounded-xl px-4 py-3"
                                                >
                                            </div>
                                            <!-- EJ Velez -->
                                            <div>
                                                <label for="ejVelez" class="block text-sm font-medium mb-2">
                                                    EJ Velez F&B
                                                </label>
                                                <input
                                                    type="text"
                                                    id="ejVelez"
                                                    name="ej_velez_fb"
                                                    placeholder="0.00"
                                                    step="0.01"
                                                   
                                                    inputmode="decimal"
                                                    class="marketing-expense w-full border rounded-xl px-4 py-3"
                                                >
                                            </div>
                                            <!-- Guest DJ -->
                                            <div>
                                                <label for="guestDJ" class="block text-sm font-medium mb-2">
                                                    Guest DJ F&B
                                                </label>
                                                <input
                                                    type="text"
                                                    id="guestDJ"
                                                    name="guest_dj_fb"
                                                    placeholder="0.00"
                                                    step="0.01"
                                                   
                                                    inputmode="decimal"
                                                    class="marketing-expense w-full border rounded-xl px-4 py-3"
                                                >
                                            </div>
                                            <!-- Others -->
                                            <div>
                                                <label for="marketingOthers" class="block text-sm font-medium mb-2">
                                                    Others
                                                </label>
                                                <input
                                                    type="text"
                                                    id="marketingOthers"
                                                    name="marketing_others"
                                                    placeholder="0.00"
                                                    step="0.01"
                                                   
                                                    inputmode="decimal"
                                                    class="marketing-expense w-full border rounded-xl px-4 py-3"
                                                >
                                            </div>
                                                                            </div>
                                        <!-- TOTAL MARKETING EXPENSES -->
                                            <div class="flex justify-end mb-6">
                                                <div class="w-full md:w-1/3">
                                                    <label
                                                        for="totalMarketingExpenses"
                                                        class="block text-sm font-semibold mb-2"
                                                    >
                                                        Total Marketing Expenses
                                                    </label>

                                                    <input
                                                        type="text"
                                                        id="totalMarketingExpenses"
                                                        name="total_marketing_expenses"
                                                        value="0.00"
                                                        readonly
                                                        class="w-full border-2 border-blue-500 rounded-xl px-4 py-3 bg-blue-50 text-blue-700 font-bold text-lg"
                                                    >
                                                </div>
                                            </div>
                                        <!-- SALES RECONCILIATION -->
                                        <div class="flex items-center gap-3 mb-4 pt-5">
                                            <span class="h-5 w-1 rounded-full bg-blue-600"></span>
                                            <h4 class="text-sm font-bold text-gray-900">Sales Reconciliation</h4>
                                            <span class="h-px flex-1 bg-gray-200"></span>
                                        </div>
                                        <div class="grid md:grid-cols-4 gap-4 mb-6">
                                            <div>
                                                <label for="unpaidAccounts" class="block text-sm font-medium mb-2">
                                                    Unpaid Accounts
                                                </label>
                                                <input
                                                    type="text"
                                                    id="unpaidAccounts"
                                                    value="0.00"
                                                    readonly
                                                    class="w-full border rounded-xl px-4 py-3 bg-gray-100 font-semibold"
                                                >
                                            </div>
                                            <div>
                                                <label for="ownersDiscount" class="block text-sm font-medium mb-2">
                                                    Owners Discount
                                                </label>
                                                <input
                                                    type="text"
                                                    id="ownersDiscount"
                                                    placeholder="0.00"
                                                    step="0.01"
                                                   
                                                    class="w-full border rounded-xl px-4 py-3"
                                                >
                                            </div>
                                            <div>
                                                <label for="paidAccounts" class="block text-sm font-medium mb-2">
                                                    Paid Accounts
                                                </label>
                                                <input
                                                    type="text"
                                                    id="paidAccounts"
                                                    placeholder="0.00"
                                                    step="0.01"
                                                   
                                                    class="w-full border rounded-xl px-4 py-3"
                                                >
                                            </div>
                                            <div>
                                                <label for="totalSalesBasedOnPayment" class="block text-sm font-medium mb-2">
                                                    Total Sales Based on Payment + Accts
                                                </label>
                                                <input
                                                    type="text"
                                                    id="totalSalesBasedOnPayment"
                                                    value="0.00"
                                                    readonly
                                                    class="w-full border rounded-xl px-4 py-3 bg-gray-100 font-semibold"
                                                >
                                            </div>
                                            <!-- Short / Over -->
                                            <div>
                                                <label for="reconShortOver" class="block text-sm font-medium mb-2">
                                                    Short / Over
                                                </label>
                                                <input
                                                    type="text"
                                                    id="reconShortOver"
                                                    value="0.00"
                                                    readonly
                                                    class="w-full border rounded-xl px-4 py-3 bg-gray-100 font-semibold"
                                                >
                                            </div>
                                            <!-- Refund -->
                                            <div>
                                                <label for="reconRefund" class="block text-sm font-medium mb-2">
                                                    Refund
                                                </label>
                                                <input
                                                    type="number"
                                                    id="reconRefund"
                                                    name="refund"
                                                    placeholder="0.00"
                                                    step="0.01"
                                                   
                                                    class="w-full border rounded-xl px-4 py-3"
                                                >
                                            </div>
                                            
                                        </div>
                                        
                                        <!-- Sales Deduction -->
                                        <div class="flex items-center gap-3 mb-4 pt-5">
                                            <span class="h-5 w-1 rounded-full bg-blue-600"></span>
                                            <h4 class="text-sm font-bold text-gray-900">Sales Deductions</h4>
                                            <span class="h-px flex-1 bg-gray-200"></span>
                                        </div>

                                        <div class="grid md:grid-cols-3 gap-4 mb-6">
                                            <div>
                                                <label for="pwdDiscount" class="block text-sm font-medium mb-2">
                                                    PWD Discount
                                                </label>
                                                    <input type="text" id="pwdDiscount" name="pwd_discount"
                                                        placeholder="0.00" step="0.01" inputmode="decimal"
                                                    class="sales-deduction w-full border rounded-xl px-4 py-3">
                                            </div>
                                            <div>
                                                <label for="seniorCitizenDiscount" class="block text-sm font-medium mb-2">
                                                    Senior Citizen Discount
                                                </label>
                                                    <input type="text" id="seniorCitizenDiscount" name="senior_citizen_discount"
                                                        placeholder="0.00" step="0.01" inputmode="decimal"
                                                    class="sales-deduction w-full border rounded-xl px-4 py-3">
                                            </div>
                                            <div>
                                                <label for="specialCustomerDiscount" class="block text-sm font-medium mb-2">
                                                    Special Customer Discount
                                                </label>
                                                    <input type="text" id="specialCustomerDiscount" name="special_customer_discount"
                                                        placeholder="0.00" step="0.01" inputmode="decimal"
                                                    class="sales-deduction w-full border rounded-xl px-4 py-3">
                                            </div>
                                        </div>
                                        <!-- Total Deduction aligned to the right -->
                                        <!-- Total Sales Deduction -->
                                        <div class="flex justify-end mb-6">
                                            <div class="w-full md:w-1/3">
                                                <label
                                                    for="totalSalesDeduction"
                                                    class="block text-sm font-semibold mb-2">
                                                    Total Sales Deduction
                                                </label>

                                                <input
                                                    type="text"
                                                    id="totalSalesDeduction"
                                                    name="total_sales_deduction"
                                                    value="0.00"
                                                    readonly
                                                    class="w-full border-2 border-blue-500 rounded-xl px-4 py-3 bg-blue-50 text-blue-700 font-bold text-lg">
                                            </div>
                                        </div>
                                        <!-- Gross Sale Highlight -->
                                        <div class="mb-6">
                                            <div class="bg-blue-50 border-2 border-blue-500 rounded-2xl p-5 shadow-sm">

                                                <div class="flex items-center justify-between gap-4">
                                                    <div>
                                                        <p class="text-sm font-semibold text-blue-700 uppercase tracking-wide">
                                                            Gross Sale
                                                        </p>
                                                        <p class="text-xs text-gray-500 mt-1">
                                                            Total sales before deductions
                                                        </p>
                                                    </div>

                                                    <div class="text-right">
                                                        <input
                                                            type="text"
                                                            id="grossSale"
                                                            name="gross_sale"
                                                            value="0.00"
                                                            readonly
                                                            class="w-48 text-right text-2xl font-bold text-blue-700 bg-transparent border-0 outline-none focus:ring-0">
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                        <!--Marketing Expenses Sales Deduction-->
                                        <div class="flex items-center gap-3 mb-4 pt-5">
                                            <span class="h-5 w-1 rounded-full bg-blue-600"></span>
                                            <h4 class="text-sm font-bold text-gray-900">Marketing Expense Deductions</h4>
                                            <span class="h-px flex-1 bg-gray-200"></span>
                                        </div>

                                        <div class="grid md:grid-cols-3 gap-4 mb-6">

                                            <!-- Owners Discount -->
                                            <div>
                                                <label for="marketingOwnersDiscount" class="block text-sm font-medium mb-2">
                                                    Owners Discount
                                                </label>
                                                <input
                                                    type="text"
                                                    id="marketingOwnersDiscount"
                                                    name="owners_discount"
                                                    value="0.00"
                                                    readonly
                                                    class="w-full border rounded-xl px-4 py-3 bg-gray-100">
                                            </div>

                                            <!-- Food & Beverage Summary -->
                                            <div>
                                                <label for="foodBeverageSummary" class="block text-sm font-medium mb-2">
                                                    Food & Beverage Summary
                                                </label>
                                                <input
                                                    type="text"
                                                    id="foodBeverageSummary"
                                                    name="beverage_sales"
                                                    value="0.00"
                                                    readonly
                                                    class="w-full border rounded-xl px-4 py-3 bg-gray-100">
                                            </div>

                                            <!-- DJs Talent Fee -->
                                            <div>
                                                <label for="djsTalentFee" class="block text-sm font-medium mb-2">
                                                    DJs Talent Fee
                                                </label>
                                                <input
                                                    type="text"
                                                    id="djsTalentFee"
                                                    name="djs_talent_fee"
                                                    placeholder="0.00"
                                                    step="0.01"
                                                   
                                                    class="w-full border rounded-xl px-4 py-3 font-semibold">
                                            </div>

                                            <!-- Bouncers Fee -->
                                            <div>
                                                <label for="bouncersFee" class="block text-sm font-medium mb-2">
                                                    Bouncers Fee
                                                </label>
                                                <input
                                                    type="text"
                                                    id="bouncersFee"
                                                    name="bouncers_fee"
                                                    placeholder="0.00"
                                                    step="0.01"
                                                   
                                                    class="w-full border rounded-xl px-4 py-3 font-semibold">
                                            </div>

                                            <!-- Others -->
                                            <div>
                                                <label for="others" class="block text-sm font-medium mb-2">
                                                    Others
                                                </label>
                                                <input
                                                    type="text"
                                                    id="others"
                                                    name="others"
                                                    placeholder="0.00"
                                                    step="0.01"
                                                   
                                                    class="w-full border rounded-xl px-4 py-3">
                                            </div>

                                        </div>

                                        <!-- Total Expenses -->
                                        <!-- Total Deduction + Total Expenses -->
                                        <div class="grid md:grid-cols-2 gap-4 mb-6">
                                            <!-- Total Sales Deduction -->
                                            <div>
                                                <label
                                                    for="totalSalesDeduction"
                                                    class="block text-sm font-semibold mb-2"
                                                >
                                                    Total Sales Deduction
                                                </label>
                                                <input
                                                    type="text"
                                                    id="totalDeductionsAndExpenses"
                                                    name="total_deductions_and_expenses"
                                                    value="0.00"
                                                    readonly
                                                    class="w-full border-2 border-blue-500 rounded-xl px-4 py-3 bg-blue-50 text-blue-700 font-bold text-lg"
                                                >
                                            </div>
                                            <!-- Total Marketing Expenses -->
                                            <div>
                                                <label
                                                    for="totalMarketingExpenses"
                                                    class="block text-sm font-semibold mb-2"
                                                >
                                                    Total Expenses
                                                </label>
                                                <input
                                                    type="text"
                                                    id="totalExpenses"
                                                    name="total_expenses"
                                                    value="0.00"
                                                    readonly
                                                    class="w-full border-2 border-blue-500 rounded-xl px-4 py-3 bg-blue-50 text-blue-700 font-bold text-lg"
                                                >
                                            </div>
                                        </div>
                                        <!-- Total Net Sale -->
                                        <div class="mb-6">
                                            <div class="bg-blue-50 border-2 border-blue-500 rounded-2xl p-5 shadow-sm">
                                                <div class="flex items-center justify-between gap-4">
                                                    <div>
                                                        <p class="text-sm font-semibold text-blue-700 uppercase tracking-wide">
                                                            Total Net Sale
                                                        </p>
                                                        <p class="text-xs text-gray-500 mt-1">
                                                            Gross sale less deductions and expenses
                                                        </p>
                                                    </div>
                                                    <div class="text-right">
                                                        <input
                                                            type="text"
                                                            id="totalNetSale"
                                                            name="total_net_sale"
                                                            value="0.00"
                                                            readonly
                                                            class="w-48 text-right text-2xl font-bold text-blue-700 bg-transparent border-0 outline-none focus:ring-0">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Tip Collected -->
                                        <div class="flex items-center gap-3 mb-4 pt-5">
                                            <span class="h-5 w-1 rounded-full bg-blue-600"></span>
                                            <h4 class="text-sm font-bold text-gray-900">Tips</h4>
                                            <span class="h-px flex-1 bg-gray-200"></span>
                                        </div>
                                        <div class="grid md:grid-cols-3 gap-4 mb-6">
                                            <!-- Cash Tips -->
                                            <div>
                                                <label for="cashSales" class="block text-sm font-medium mb-2">
                                                    Cash Sales Based On POS
                                                </label>
                                                <input
                                                    type="number"
                                                    id="cashSales"
                                                    name="cash_sales"
                                                    placeholder="0.00"
                                                    step="0.01"
                                                   
                                                    class="w-full border rounded-xl px-4 py-3">
                                            </div>
                                            <!-- Card Tips -->
                                            <div>
                                            <label for="grabSaleGross" class="block text-sm font-medium mb-2">
                                                Grab Sale Gross
                                            </label>
                                            <input
                                                type="text"
                                                id="grabSaleGross"
                                                name="grab_sale_gross"
                                                placeholder="0.00"
                                                step="0.01"
                                               
                                                class="w-full border rounded-xl px-4 py-3">
                                        </div>

                                        <!-- Online Tips -->
                                        <div>
                                            <label for="onlineTips" class="block text-sm font-medium mb-2">
                                                Grab Sale Less 27%
                                            </label>
                                                <input
                                                    type="text"
                                                id="onlineTips"
                                                name="online_tips"
                                                value="0.00"
                                                inputmode="decimal"
                                                placeholder="0.00"
                                                    readonly
                                                class="w-full border rounded-xl px-4 py-3">
                                        </div>
                                    </div>
                                    <!-- Cash Remitted Shortage -->
                                    <div class="flex justify-end mb-6">
                                        <div class="w-full md:w-1/3">
                                            <label for="cashRemittedShortage" class="block text-sm font-semibold mb-2"> 
                                                <!-- Cash Remitted Shortage -->
                                                Total Cash Remits
                                            </label>

                                            <input
                                                type="text"
                                                id="cashRemittedShortage"
                                                name="cash_remitted_shortage"
                                                value="0.00"
                                                readonly
                                                class="w-full border-2 border-blue-500 rounded-xl px-4 py-3 bg-blue-50 text-blue-700 font-bold text-lg">
                                        </div>
                                    </div>

                                    <div class="sticky bottom-0 -mx-4 mt-8 flex justify-end gap-3 border-t border-gray-200 bg-white/95 px-4 py-4 backdrop-blur sm:-mx-6 sm:px-6 md:-mx-8 md:px-8">
                                        <button type="submit"
                                                class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-500/20">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            Save Sale
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>   
                    </div>
                </form>

                    </div>
                </div>
            </div>
        </div>  

