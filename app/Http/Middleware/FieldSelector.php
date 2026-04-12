<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FieldSelector
{
    /**
     * Handle an incoming request and apply field selection for optimized responses.
     *
     * Usage: GET /api/v1/student/profile?fields=id,student_number,faculty.name
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Apply field selection if requested
        $fields = $request->query('fields');

        if ($fields && $response->getStatusCode() === 200) {
            $originalData = $response->getData(true);

            if (is_array($originalData)) {
                $filteredData = $this->filterFields($originalData, $fields);
                $response->setData($filteredData);

                $response->headers->set('X-Fields-Filtered', 'true');
            }
        }

        return $response;
    }

    /**
     * Filter response data to only include requested fields.
     */
    protected function filterFields(array $data, string $fields): array
    {
        $requestedFields = array_map('trim', explode(',', $fields));
        $filtered = [];

        foreach ($requestedFields as $field) {
            $parts = explode('.', $field);

            if (count($parts) === 1) {
                // Simple field
                if (array_key_exists($parts[0], $data)) {
                    $filtered[$parts[0]] = $data[$parts[0]];
                }
            } else {
                // Nested field (e.g., faculty.name)
                $parent = $parts[0];
                $child = $parts[1];

                if (isset($data[$parent]) && is_array($data[$parent])) {
                    if (!isset($filtered[$parent])) {
                        $filtered[$parent] = [];
                    }

                    if (array_key_exists($child, $data[$parent])) {
                        $filtered[$parent][$child] = $data[$parent][$child];
                    }
                }
            }
        }

        return array_merge($data, $filtered);
    }
}
