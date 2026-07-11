<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'client_id',
        'invoice_number',
        'issue_date',
        'due_date',
        'status',
        'total',
        'notes',
        'tax_rate',
        'discount',
        'paid_date',
        'amount_paid',
        'payment_mode',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments()
    {
        return $this->hasMany(InvoicePayment::class);
    }

    public function updatePaymentStatus(): void
    {
        $this->amount_paid = round(floatval($this->payments()->sum('amount')), 2);
        if ($this->amount_paid >= $this->total) {
            $this->status = 'paid';
            $this->paid_date = now()->format('Y-m-d');
        } elseif ($this->amount_paid > 0) {
            $this->status = 'partially_paid';
        } else {
            $this->status = 'draft';
            $this->paid_date = null;
        }
        $this->save();
    }

    public function recalculateTotal(): void
    {
        $subtotal = $this->items()->sum('line_total');
        $discountAmount = $subtotal * (floatval($this->discount ?? 0) / 100);
        $taxableAmount = $subtotal - $discountAmount;
        $taxAmount = $taxableAmount * (floatval($this->tax_rate ?? 0) / 100);
        $this->total = round($taxableAmount + $taxAmount, 2);
        $this->save();
    }

    public static function formatINR($amount): string
    {
        $amount = floatval($amount);
        if (class_exists('\NumberFormatter')) {
            $formatter = new \NumberFormatter('en_IN', \NumberFormatter::DECIMAL);
            $formatter->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, 2);
            $formatter->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, 2);
            return '₹' . $formatter->format($amount);
        }
        
        $parts = explode('.', strval(round($amount, 2)));
        $num = $parts[0];
        $decimal = isset($parts[1]) ? '.' . str_pad($parts[1], 2, '0', STR_PAD_RIGHT) : '.00';
        
        $len = strlen($num);
        if ($len <= 3) {
            return '₹' . $num . $decimal;
        }
        $last_three = substr($num, -3);
        $rest = substr($num, 0, -3);
        $rest = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $rest);
        return '₹' . $rest . ',' . $last_three . $decimal;
    }
}
