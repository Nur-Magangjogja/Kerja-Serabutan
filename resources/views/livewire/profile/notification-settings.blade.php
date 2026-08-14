<div class="space-y-4">
    @if(session()->has('message'))
        <div class="p-3 bg-green-50 border border-green-200 rounded-xl text-sm text-green-600">
            {{ session('message') }}
        </div>
    @endif

    <!-- General Notification -->
    <div class="flex items-center justify-between py-3 border-b border-gray-100">
        <span class="font-medium text-gray-900">General Notification</span>
        <button wire:click="updateSetting('generalNotification')"
            class="relative inline-flex h-6 w-11 items-center rounded-full transition {{ $generalNotification ? 'bg-primary-500' : 'bg-gray-300' }}">
            <span
                class="inline-block h-4 w-4 transform rounded-full bg-white transition {{ $generalNotification ? 'translate-x-6' : 'translate-x-1' }}"></span>
        </button>
    </div>

    <!-- Sound -->
    <div class="flex items-center justify-between py-3 border-b border-gray-100">
        <span class="font-medium text-gray-900">Sound</span>
        <button wire:click="updateSetting('sound')"
            class="relative inline-flex h-6 w-11 items-center rounded-full transition {{ $sound ? 'bg-primary-500' : 'bg-gray-300' }}">
            <span
                class="inline-block h-4 w-4 transform rounded-full bg-white transition {{ $sound ? 'translate-x-6' : 'translate-x-1' }}"></span>
        </button>
    </div>

    <!-- Sound Call -->
    <div class="flex items-center justify-between py-3 border-b border-gray-100">
        <span class="font-medium text-gray-900">Sound Call</span>
        <button wire:click="updateSetting('soundCall')"
            class="relative inline-flex h-6 w-11 items-center rounded-full transition {{ $soundCall ? 'bg-primary-500' : 'bg-gray-300' }}">
            <span
                class="inline-block h-4 w-4 transform rounded-full bg-white transition {{ $soundCall ? 'translate-x-6' : 'translate-x-1' }}"></span>
        </button>
    </div>

    <!-- Vibrate -->
    <div class="flex items-center justify-between py-3 border-b border-gray-100">
        <span class="font-medium text-gray-900">Vibrate</span>
        <button wire:click="updateSetting('vibrate')"
            class="relative inline-flex h-6 w-11 items-center rounded-full transition {{ $vibrate ? 'bg-primary-500' : 'bg-gray-300' }}">
            <span
                class="inline-block h-4 w-4 transform rounded-full bg-white transition {{ $vibrate ? 'translate-x-6' : 'translate-x-1' }}"></span>
        </button>
    </div>

    <!-- Transaction Update -->
    <div class="flex items-center justify-between py-3 border-b border-gray-100">
        <span class="font-medium text-gray-900">Transaction Update</span>
        <button wire:click="updateSetting('transactionUpdate')"
            class="relative inline-flex h-6 w-11 items-center rounded-full transition {{ $transactionUpdate ? 'bg-primary-500' : 'bg-gray-300' }}">
            <span
                class="inline-block h-4 w-4 transform rounded-full bg-white transition {{ $transactionUpdate ? 'translate-x-6' : 'translate-x-1' }}"></span>
        </button>
    </div>

    <!-- Expense Reminder -->
    <div class="flex items-center justify-between py-3 border-b border-gray-100">
        <span class="font-medium text-gray-900">Expense Reminder</span>
        <button wire:click="updateSetting('expenseReminder')"
            class="relative inline-flex h-6 w-11 items-center rounded-full transition {{ $expenseReminder ? 'bg-primary-500' : 'bg-gray-300' }}">
            <span
                class="inline-block h-4 w-4 transform rounded-full bg-white transition {{ $expenseReminder ? 'translate-x-6' : 'translate-x-1' }}"></span>
        </button>
    </div>

    <!-- Budget Notifications -->
    <div class="flex items-center justify-between py-3 border-b border-gray-100">
        <span class="font-medium text-gray-900">Budget Notifications</span>
        <button wire:click="updateSetting('budgetNotifications')"
            class="relative inline-flex h-6 w-11 items-center rounded-full transition {{ $budgetNotifications ? 'bg-primary-500' : 'bg-gray-300' }}">
            <span
                class="inline-block h-4 w-4 transform rounded-full bg-white transition {{ $budgetNotifications ? 'translate-x-6' : 'translate-x-1' }}"></span>
        </button>
    </div>

    <!-- Low Balance Alerts -->
    <div class="flex items-center justify-between py-3">
        <span class="font-medium text-gray-900">Low Balance Alerts</span>
        <button wire:click="updateSetting('lowBalanceAlerts')"
            class="relative inline-flex h-6 w-11 items-center rounded-full transition {{ $lowBalanceAlerts ? 'bg-primary-500' : 'bg-gray-300' }}">
            <span
                class="inline-block h-4 w-4 transform rounded-full bg-white transition {{ $lowBalanceAlerts ? 'translate-x-6' : 'translate-x-1' }}"></span>
        </button>
    </div>
</div>