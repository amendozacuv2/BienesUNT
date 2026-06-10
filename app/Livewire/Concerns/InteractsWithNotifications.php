<?php

namespace App\Livewire\Concerns;

trait InteractsWithNotifications
{
    protected function notifySuccess(string $message): void
    {
        $this->dispatchNotification('success', $message);
    }

    protected function notifyError(string $message): void
    {
        $this->dispatchNotification('error', $message);
    }

    protected function notifyWarning(string $message): void
    {
        $this->dispatchNotification('warning', $message);
    }

    protected function notifyInfo(string $message): void
    {
        $this->dispatchNotification('info', $message);
    }

    protected function flashSuccess(string $message): void
    {
        $this->flashNotification('success', $message);
    }

    protected function flashError(string $message): void
    {
        $this->flashNotification('error', $message);
    }

    protected function flashWarning(string $message): void
    {
        $this->flashNotification('warning', $message);
    }

    protected function flashInfo(string $message): void
    {
        $this->flashNotification('info', $message);
    }

    private function dispatchNotification(string $icon, string $message): void
    {
        $this->dispatch('app-notify', icon: $icon, message: $message);
    }

    private function flashNotification(string $icon, string $message): void
    {
        session()->flash('notification', compact('icon', 'message'));
    }
}
