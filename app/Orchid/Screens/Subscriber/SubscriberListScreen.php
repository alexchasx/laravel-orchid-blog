<?php

namespace App\Orchid\Screens\Subscriber;

use App\Models\Subscriber;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class SubscriberListScreen extends Screen
{
    /**
     * Query data.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'subscribers' => Subscriber::filters()
                ->defaultSort('created_at', 'desc')
                ->paginate(24),
        ];
    }

    /**
     * Display header name.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Подписчики рассылки';
    }

    /**
     * Button commands.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [];
    }

    /**
     * Views.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [
            Layout::table('subscribers', [

                TD::make('id', 'ID')->sort(),

                TD::make('email', 'Email')
                    ->render(fn (Subscriber $subscriber) => e($subscriber->email)),

                TD::make('status', 'Статус')
                    ->render(function (Subscriber $subscriber) {
                        if ($subscriber->isActive()) {
                            return '<span style="color:#00d68f;">Активен</span>';
                        }

                        return '<span style="color:#e5484d;">Отписан</span>';
                    }),

                TD::make('user_id', 'Пользователь')
                    ->render(function (Subscriber $subscriber) {
                        return $subscriber->user?->name ?? '—';
                    }),

                TD::make('created_at', 'Дата подписки')
                    ->render(function (Subscriber $subscriber) {
                        return Carbon::parse($subscriber->created_at)->format('d.m.Y H:i');
                    }),

                TD::make('Удалить')
                    ->render(function (Subscriber $subscriber) {
                        return Button::make('')
                            ->icon('trash')
                            ->method('remove')
                            ->confirm('Удалить подписчика ' . $subscriber->email . '?')
                            ->parameters(['subscriber' => $subscriber->id]);
                    }),
            ]),
        ];
    }

    public function remove(Request $request): void
    {
        Subscriber::findOrFail($request->integer('subscriber'))->delete();

        Toast::info(__('Подписчик удалён.'));
    }
}
