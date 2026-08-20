import { initFlowbite } from 'flowbite';

// Expose initFlowbite globally so Livewire components and Alpine can trigger it
window.initFlowbite = initFlowbite;

// Initialize Flowbite on standard DOM load
document.addEventListener('DOMContentLoaded', () => {
    initFlowbite();
});

// Re-initialize Flowbite after Livewire navigation / updates (SPA navigation & dynamic DOM changes)
document.addEventListener('livewire:navigated', () => {
    initFlowbite();
});

window.addEventListener('livewire:load', () => {
    initFlowbite();
});

window.addEventListener('livewire:update', () => {
    initFlowbite();
});

// Custom event to manually re-init Flowbite if needed
window.addEventListener('init-flowbite', () => {
    initFlowbite();
});

export { initFlowbite };
