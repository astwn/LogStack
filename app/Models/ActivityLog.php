<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'username',
        'app',
        'action',
        'description',
        'ip_address',
        'user_agent',
    ];
    protected $casts = [
        'created_at' => 'datetime',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    // Scope filter by app
    public function scopeByApp($query, $app)
    {
        return $query->where('app', $app);
    }
    // Scope filter by username
    public function scopeByUsername($query, $username)
    {
        return $query->where('username', 'like', "%{$username}%");
    }
    // Scope filter by date range
    public function scopeByDateRange($query, $from, $to)
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }
    // Label warna untuk action
    public function getActionBadgeAttribute(): string
    {
        return match($this->action) {
            'login'      => 'emerald',
            'logout'     => 'red',
            'open_app'   => 'blue',
            'send_email' => 'orange',
            default      => 'slate',
        };
    }
    // Label icon untuk app
    public function getAppIconAttribute(): string
    {
        return match($this->app) {
            'laravel'   => 'fa-layer-group',
            'sogo'      => 'fa-envelope',
            'nextcloud' => 'fa-cloud',
            'odoo'      => 'fa-briefcase',
            default     => 'fa-circle',
        };
    }
}
