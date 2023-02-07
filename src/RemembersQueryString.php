<?php

namespace BlackLion\LaravelUtils;

trait RemembersQueryString
{
    public $remember;

    protected $queryStringRememberQueryString = [
        'remember',
    ];

    public function mountRemembersQueryString()
    {
        if (request('remember') === 'forget') {
            $this->forgetQueryString();

            return;
        }

        $data = session()->get($this->getRememberQueryStringSessionName()) ?? [];
        $instance = new static($this->id);

        $this->getQueryStringKeysToRemember()->each(function ($key) use ($data, $instance) {
            if (isset($data[$key]) && $this->{$key} === $instance->{$key}) {
                if ($key === 'page' && in_array('Livewire\WithPagination', class_uses_recursive($this))) {
                    $this->setPage($data[$key]);
                } else {
                    $this->{$key} = $data[$key];
                }
            }
        });
    }

    public function dehydrateRemembersQueryString()
    {
        session()->put(
            $this->getRememberQueryStringSessionName(),
            $this->getQueryStringKeysToRemember()->mapWithKeys(function ($key) {
                return [$key => $this->{$key}];
            })->toArray(),
        );
    }

    public function forgetQueryString()
    {
        session()->remove($this->getRememberQueryStringSessionName());
        $this->remember = null;

        $this->getQueryStringKeysToRemember()->each(function ($key) {
            if ($key === 'page' && in_array('Livewire\WithPagination', class_uses_recursive($this))) {
                $this->resetPage();
            } else {
                $this->reset($key);
            }
        });
    }

    protected function getRememberQueryStringSessionName()
    {
        return 'remember_query_string.'.str_replace('.', '-', $this->getName());
    }

    protected function getQueryStringKeysToRemember()
    {
        return collect($this->getQueryString())
            ->map(function ($value, $key) {
                if (! is_string($key)) {
                    $key = $value;
                    $value = [];
                }

                if ($key !== 'remember' && ($value['remember'] ?? true)) {
                    return $key;
                }
            })
            ->filter()
            ->values();
    }
}
