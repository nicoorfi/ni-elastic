<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class IndexingPlan extends Model
{
    public const TYPES = ['file'];

    public const FREQUENCIES = ['daily', 'weekly', 'monthly', 'never'];

    use HasFactory;

    public function cluster()
    {
        return $this->belongsTo(Cluster::class);
    }

    public function details()
    {
        return $this->hasMany(IndexingPlanDetails::class, 'indexing_plan_id');
    }

    /**
     * Scope a query to only include users of a given type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     */
    public function scopeForProject($query, Project $project)
    {
        return $query->leftJoin('clusters', 'indexing_plans.cluster_id', '=', 'clusters.id')
            ->where('clusters.project_id', '=', $project->id);
    }
}
