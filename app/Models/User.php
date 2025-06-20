<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
// Antar Bank
  public function transferRecipients()
{
    return $this->hasMany(\App\Models\TransferRecipient::class);
}

public function transferTransactions()
{
    return $this->hasMany(\App\Models\TransferTransaction::class);
}

// Beda bank
public function bankRecipients()
{
    return $this->hasMany(BankRecipient::class);
}

public function interbankTransactions()
{
    return $this->hasMany(InterbankTransaction::class);
}
}
