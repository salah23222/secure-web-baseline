<?php

declare(strict_types=1);

namespace Tests\Core;

use App\Core\Validator;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    // ── required ────────────────────────────────────────────────────

    public function testRequiredPassesWhenValuePresent(): void
    {
        $v = Validator::make(['name' => 'Salah'])->required('name', 'Name');
        $this->assertTrue($v->passes());
    }

    public function testRequiredFailsWhenValueMissing(): void
    {
        $v = Validator::make([])->required('name', 'Name');
        $this->assertTrue($v->fails());
        $this->assertStringContainsString('required', $v->firstError());
    }

    public function testRequiredFailsOnEmptyString(): void
    {
        $v = Validator::make(['name' => '   '])->required('name', 'Name');
        $this->assertTrue($v->fails());
    }

    // ── email ────────────────────────────────────────────────────────

    public function testEmailPassesValidAddress(): void
    {
        $v = Validator::make(['email' => 'user@example.com'])->email('email');
        $this->assertTrue($v->passes());
    }

    public function testEmailFailsInvalidAddress(): void
    {
        $v = Validator::make(['email' => 'not-an-email'])->email('email', 'Email');
        $this->assertTrue($v->fails());
    }

    public function testEmailSkipsEmptyValue(): void
    {
        // Empty value — required rule should catch this, email rule skips it
        $v = Validator::make(['email' => ''])->email('email');
        $this->assertTrue($v->passes());
    }

    // ── minLength ────────────────────────────────────────────────────

    public function testMinLengthPassesLongEnoughValue(): void
    {
        $v = Validator::make(['password' => 'secret12'])->minLength('password', 8);
        $this->assertTrue($v->passes());
    }

    public function testMinLengthFailsShortValue(): void
    {
        $v = Validator::make(['password' => 'short'])->minLength('password', 8, 'Password');
        $this->assertTrue($v->fails());
        $this->assertStringContainsString('8', $v->firstError());
    }

    // ── maxLength ────────────────────────────────────────────────────

    public function testMaxLengthPassesShortValue(): void
    {
        $v = Validator::make(['name' => 'Ali'])->maxLength('name', 150);
        $this->assertTrue($v->passes());
    }

    public function testMaxLengthFailsLongValue(): void
    {
        $v = Validator::make(['name' => str_repeat('a', 151)])->maxLength('name', 150, 'Name');
        $this->assertTrue($v->fails());
    }

    // ── confirmed ────────────────────────────────────────────────────

    public function testConfirmedPassesMatchingValues(): void
    {
        $v = Validator::make([
            'password'              => 'secret12',
            'password_confirmation' => 'secret12',
        ])->confirmed('password', 'password_confirmation');

        $this->assertTrue($v->passes());
    }

    public function testConfirmedFailsMismatch(): void
    {
        $v = Validator::make([
            'password'              => 'secret12',
            'password_confirmation' => 'different',
        ])->confirmed('password', 'password_confirmation');

        $this->assertTrue($v->fails());
    }

    // ── in ───────────────────────────────────────────────────────────

    public function testInPassesAllowedValue(): void
    {
        $v = Validator::make(['role' => 'admin'])->in('role', ['admin', 'user']);
        $this->assertTrue($v->passes());
    }

    public function testInFailsDisallowedValue(): void
    {
        $v = Validator::make(['role' => 'superuser'])->in('role', ['admin', 'user'], 'Role');
        $this->assertTrue($v->fails());
    }

    // ── integer / numeric ────────────────────────────────────────────

    public function testIntegerPassesValidInt(): void
    {
        $v = Validator::make(['age' => '25'])->integer('age');
        $this->assertTrue($v->passes());
    }

    public function testIntegerFailsNonInt(): void
    {
        $v = Validator::make(['age' => 'abc'])->integer('age', 'Age');
        $this->assertTrue($v->fails());
    }

    public function testNumericPassesFloat(): void
    {
        $v = Validator::make(['price' => '9.99'])->numeric('price');
        $this->assertTrue($v->passes());
    }

    // ── regex ────────────────────────────────────────────────────────

    public function testRegexPassesMatchingValue(): void
    {
        $v = Validator::make(['code' => 'ABC123'])
            ->regex('code', '/^[A-Z0-9]+$/', 'Invalid code format.');
        $this->assertTrue($v->passes());
    }

    public function testRegexFailsNonMatchingValue(): void
    {
        $v = Validator::make(['code' => 'abc!'])
            ->regex('code', '/^[A-Z0-9]+$/', 'Invalid code format.');
        $this->assertTrue($v->fails());
        $this->assertSame('Invalid code format.', $v->firstError());
    }

    // ── multiple errors ──────────────────────────────────────────────

    public function testMultipleErrorsCollected(): void
    {
        $v = Validator::make([])
            ->required('name', 'Name')
            ->required('email', 'Email')
            ->required('password', 'Password');

        $this->assertCount(3, $v->errors());
    }

    // ── sanitize ─────────────────────────────────────────────────────

    public function testSanitizeEscapesHtml(): void
    {
        $result = Validator::sanitize('<script>alert(1)</script>');
        $this->assertStringNotContainsString('<script>', $result);
    }
}
