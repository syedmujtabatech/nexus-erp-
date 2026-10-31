@extends('layouts.app')

@section('title', 'My Account Profile & Settings')

@section('content')
<div class="row">
    <!-- Left Column: User Overview Card -->
    <div class="col-md-4 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-body text-center p-4">
                <div class="position-relative d-inline-block mb-3">
                    @if($user->avatar)
                        <img src="{{ asset($user->avatar) }}" alt="Avatar" id="avatarPreview" class="rounded-circle shadow-sm border border-3 border-primary" style="width: 120px; height: 120px; min-width: 120px; min-height: 120px; max-width: 120px; max-height: 120px; object-fit: cover; object-position: center 20%;" />
                    @else
                        <div id="avatarFallback" class="avatar avatar-xl rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto shadow-sm" style="width: 120px; height: 120px; font-size: 3rem; font-weight: bold;">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                    @endif
                </div>

                <h4 class="fw-bold mb-1" style="color: #0f172a;">{{ $user->name }}</h4>
                <p class="text-muted small mb-2"><i class="bx bx-envelope me-1"></i> {{ $user->email }}</p>
                <div class="d-flex justify-content-center gap-2 mb-3">
                    <span class="badge bg-label-primary px-3 py-2 fw-semibold">
                        <i class="bx bx-shield-quarter me-1"></i> {{ $user->role->name ?? 'User' }}
                    </span>
                    @if($company)
                        @php $plan = $company->plan ?? 'trial'; @endphp
                        @if($plan === 'advance')
                            <span class="badge bg-label-success px-3 py-2 fw-semibold"><i class="bx bx-crown me-1"></i> ADVANCE PRO</span>
                        @elseif($plan === 'basic')
                            <span class="badge bg-label-info px-3 py-2 fw-semibold"><i class="bx bx-check-shield me-1"></i> BASIC PLAN</span>
                        @else
                            <span class="badge bg-label-warning px-3 py-2 fw-semibold"><i class="bx bx-time me-1"></i> TRIAL WORKSPACE</span>
                        @endif
                    @endif
                </div>

                <hr class="my-3">

                <div class="text-start small">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Phone:</span>
                        <span class="fw-semibold">{{ $user->phone ?? 'Not set' }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Active Company:</span>
                        <span class="fw-semibold text-primary">{{ $company->name ?? 'Global' }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Member Since:</span>
                        <span class="fw-semibold">{{ $user->created_at->format('M d, Y') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Edit Profile & Password Forms -->
    <div class="col-md-8">
        <!-- Edit Profile Form Card -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center">
                <i class="bx bx-user-pin text-primary fs-4 me-2"></i>
                <h5 class="mb-0 fw-bold">Personal Profile Information</h5>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold text-muted small text-uppercase">Full Name</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class="bx bx-user"></i></span>
                                <input type="text" name="name" class="form-control border-start-0" value="{{ old('name', $user->name) }}" required />
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold text-muted small text-uppercase">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class="bx bx-envelope"></i></span>
                                <input type="email" name="email" class="form-control border-start-0" value="{{ old('email', $user->email) }}" required />
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold text-muted small text-uppercase">Phone Number</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class="bx bx-phone"></i></span>
                                <input type="text" name="phone" class="form-control border-start-0" value="{{ old('phone', $user->phone) }}" placeholder="+123456789" />
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold text-muted small text-uppercase">Upload Profile Avatar</label>
                            <input type="file" name="avatar" class="form-control" accept="image/*" onchange="previewAvatar(this)" />
                            <small class="text-muted">JPG, PNG, GIF, or SVG (Max 4MB)</small>
                        </div>
                    </div>

                    <div class="text-end mt-2">
                        <button type="submit" class="btn btn-primary px-4 fw-semibold">
                            <i class="bx bx-save me-1"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Security & Password Card -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center">
                <i class="bx bx-lock-alt text-primary fs-4 me-2"></i>
                <h5 class="mb-0 fw-bold">Change Account Password</h5>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('profile.password') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold text-muted small text-uppercase">Current Password</label>
                            <input type="password" name="current_password" class="form-control" placeholder="••••••••" required />
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold text-muted small text-uppercase">New Password</label>
                            <input type="password" name="password" class="form-control" placeholder="••••••••" required />
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold text-muted small text-uppercase">Confirm New Password</label>
                            <input type="password" name="password_confirmation" class="form-control" placeholder="••••••••" required />
                        </div>
                    </div>

                    <div class="text-end mt-2">
                        <button type="submit" class="btn btn-outline-primary px-4 fw-semibold">
                            <i class="bx bx-key me-1"></i> Update Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function previewAvatar(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            var preview = document.getElementById('avatarPreview');
            var fallback = document.getElementById('avatarFallback');
            if (preview) {
                preview.src = e.target.result;
            } else if (fallback) {
                var img = document.createElement('img');
                img.id = 'avatarPreview';
                img.src = e.target.result;
                img.className = 'rounded-circle shadow-sm border border-3 border-primary';
                img.style.width = '120px';
                img.style.height = '120px';
                img.style.minWidth = '120px';
                img.style.minHeight = '120px';
                img.style.objectFit = 'cover';
                img.style.objectPosition = 'center 20%';
                fallback.parentNode.replaceChild(img, fallback);
            }
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endsection
