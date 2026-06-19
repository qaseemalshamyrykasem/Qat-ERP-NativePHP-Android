@extends('layouts.app')
@section('title', 'صلاحيات المستخدمين')
@section('content')
<div class="container-fluid py-4">
    <h2><i class="bi bi-shield-lock text-success"></i> إدارة الصلاحيات</h2>
    <div class="card shadow-sm mt-3">
        <div class="card-body">
            <p class="text-muted">يتم تحميل الصلاحيات من <code>/api/v1/permissions</code></p>
            <div id="dataGrid">جارٍ التحميل...</div>
        </div>
    </div>
</div>
@endsection
