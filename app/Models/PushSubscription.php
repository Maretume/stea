<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PushSubscription extends Model
{
    use HasFactory;

    protected $table = 'langganan_push';

    protected $fillable = [
        'id_pengguna', // user_id
        'endpoint',
        'kunci_publik', // public_key
        'token_autentikasi', // auth_token
        'enkoding_konten', // content_encoding
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'id_pengguna');
    }

    // Helper methods
    public function getSubscriptionArray()
    {
        return [
            'endpoint' => $this->endpoint,
            'keys' => [
                'p256dh' => $this->kunci_publik, // public_key -> kunci_publik
                'auth' => $this->token_autentikasi, // auth_token -> token_autentikasi
            ],
            'contentEncoding' => $this->enkoding_konten ?? 'aesgcm', // content_encoding -> enkoding_konten
        ];
    }
}
