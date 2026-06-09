<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class OnboardingSession extends Model
{
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'user_id',
        'current_step',
        'payload',
        'risk_level',
        'status',
        'signature_path',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
            if (empty($model->payload)) {
                $user = \App\Models\User::find($model->user_id);
                if ($user) {
                    $model->payload = [
                        'nom' => $user->last_name,
                        'prenom' => $user->first_name,
                        'email' => $user->email,
                        'tel' => $user->phone,
                        'pays_residence' => $user->country,
                        'adresse' => $user->city,
                    ];
                }
            }
        });
    }

    public function getReferenceAttribute()
    {
        if (!$this->created_at) {
            return 'KYC-' . now()->format('Ymd') . '-001';
        }

        $dateStr = $this->created_at->format('Ymd');
        $dateStart = $this->created_at->format('Y-m-d 00:00:00');
        $dateEnd = $this->created_at->format('Y-m-d 23:59:59');

        // Find all sessions created on the same day, ordered by created_at, then id
        $sessions = self::whereBetween('created_at', [$dateStart, $dateEnd])
            ->orderBy('created_at', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $index = 1;
        foreach ($sessions as $session) {
            if ($session->id === $this->id) {
                break;
            }
            $index++;
        }

        return sprintf('KYC-%s-%03d', $dateStr, $index);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
