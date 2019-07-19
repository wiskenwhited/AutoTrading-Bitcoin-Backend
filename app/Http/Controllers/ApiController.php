<?php

namespace App\Http\Controllers;

use App\Auth\Auth;
use App\Services\BillingService;
use App\Views\AbstractView;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ApiController extends BaseController
{
    protected function getPaginationData(Request $request, $defaults = [])
    {
        $page = $request->get('page', []);
        return [
            'limit' => intval(array_get($page, 'limit', array_get($defaults, 'limit', config('api.page.limit')))) ?: NULL,
            'offset' => intval(array_get($page, 'offset', config('api.page.offset'))) ?: 0
        ];
    }

    protected function getSortingData(Request $request, AbstractView $view = null)
    {
        $sort = [];
        if ($request->has('sort')) {
            foreach (explode(',', $request->get('sort')) as $field) {
                if (strpos($field, '-') === 0) {
                    $sort[substr($field, 1)] = 'DESC';
                } else {
                    $sort[$field] = 'ASC';
                }
            }
        }

        if ($view) {
            $sort = $view->derender($sort);
        }

        return $sort;
    }

    protected function getFilterData(Request $request)
    {
        return $request->get('filter', []);
    }

    protected function applyPaginationData(Request $request, $query, $defaults = [], $view = null)
    {
        $page = $this->getPaginationData($request, array_get($defaults, 'page', []));
        if (!is_null($page['limit']) && !is_null($page['offset'])) {
                $query->limit($page['limit'])
                    ->offset($page['offset']);
        }
        $sortingData = $this->getSortingData($request, $view);
        foreach ($sortingData as $field => $direction) {
            $query->orderBy($field, $direction);
        }

        return $query;
    }

    protected function getResponseMetadata(Request $request, $total = null)
    {
        $page = $this->getPaginationData($request);
        $page['total_pages'] = ceil((float)$total / (float)$page['limit']) ?: 1;
        return [
            'page' => $page,
            'sort' => $request->get('sort'),
            'filter' => $this->getFilterData($request)
        ];
    }

    protected function validateUserAccess($userIds, Auth $auth)
    {
        if (! is_array($userIds)) {
            $userIds = [$userIds];
        }
        foreach ($userIds as $userId) {
            if ($userId != $auth->user()->id) {
                throw new HttpException(403);
            }
        }
    }

    protected function hasActivePackage(Auth $auth, $exchange, $test = false){
        $billingService = new BillingService();
        if(!$billingService->hasActivePackage($auth, $exchange, $test))
            throw new HttpException(422, 'No active package');

    }
}
