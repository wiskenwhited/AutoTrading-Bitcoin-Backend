<?php

namespace App\Views;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class AbstractView
{
    protected $fields = [];

    protected $map = [];

    protected $calculated = [];

    protected $cache = [];

    public function render($modelOrCollection)
    {
        if ($modelOrCollection instanceof Collection) {
            return $this->renderCollection($modelOrCollection->toArray());
        } elseif (is_array($modelOrCollection)) {
            return $this->renderCollection($modelOrCollection);
        }

        return $this->renderModel($modelOrCollection);
    }

    protected function renderCollection($collection)
    {
        return array_map(function ($model) {
            return $this->renderModel($model);
        }, $collection);
    }

    protected function renderModel($model)
    {
        $this->cache = [];
        if ($model instanceof Model) {
            $data = $model->toArray();
        } elseif (is_array($model)) {
            $data = $model;
        } else {
            throw new \InvalidArgumentException("Can't handle parameter passed to renderModel");
        }

        foreach ($this->map as $original => $target) {
            $data[$target] = array_get($data, $original);
        }
        $result = [];
        foreach ($this->fields as $field) {
            $method = 'get' . camel_case($field) . 'Attribute';
            if (method_exists($this, $method)) {
                $result[$field] = $this->$method($model);
            } else {
                $result[$field] = array_get($data, $field);
            }
        }

        return $result;
    }

    public function derender($data)
    {
        $result = [];
        $map = array_flip($this->map);
        foreach ($data as $key => $value) {
            if ($name = array_get($map, $key)) {
                $result[$name] = $value;
            } elseif (! in_array($key, $this->calculated)) {
                $result[$key] = $value;
            }
        }

        return $result;
    }
}