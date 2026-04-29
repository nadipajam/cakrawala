@csrf
@if ($method === 'PUT')
    @method('PUT')
@endif

<div class="grid gap-4 md:grid-cols-2">
    <div>
        <label class="admin-label" for="name">Name</label>
        <input id="name" name="name" value="{{ old('name', $user->name) }}" class="admin-field" required>
    </div>
    <div>
        <label class="admin-label" for="email">Email</label>
        <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" class="admin-field" required>
    </div>
    <div>
        <label class="admin-label" for="phone">Phone</label>
        <input id="phone" name="phone" value="{{ old('phone', $user->phone) }}" class="admin-field">
    </div>
    <div>
        <label class="admin-label" for="role">Role</label>
        <select id="role" name="role" class="admin-field" required>
            @foreach ($roleOptions as $roleKey => $roleOption)
                <option value="{{ $roleKey }}" @selected(old('role', $user->role ?: \App\Support\UserRole::CUSTOMER) === $roleKey)>{{ $roleOption['label'] }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="admin-label" for="employee_id">Employee ID</label>
        <input id="employee_id" name="employee_id" value="{{ old('employee_id', $user->employee_id) }}" class="admin-field" placeholder="Auto generated if empty">
    </div>
    <div>
        <label class="admin-label" for="department">Department</label>
        <input id="department" name="department" value="{{ old('department', $user->department) }}" class="admin-field" placeholder="Operations, Revenue, Support">
    </div>
    <div>
        <label class="admin-label" for="job_title">Job Title</label>
        <input id="job_title" name="job_title" value="{{ old('job_title', $user->job_title) }}" class="admin-field" placeholder="Duty Officer, Analyst, Supervisor">
    </div>
    <div>
        <label class="admin-label" for="password">{{ $method === 'PUT' ? 'New Password' : 'Password' }}</label>
        <input id="password" name="password" type="password" class="admin-field" {{ $method === 'POST' ? 'required' : '' }}>
    </div>
    <div>
        <label class="admin-label" for="password_confirmation">Confirm Password</label>
        <input id="password_confirmation" name="password_confirmation" type="password" class="admin-field" {{ $method === 'POST' ? 'required' : '' }}>
    </div>
</div>

<div class="mt-5 flex flex-wrap items-center gap-2">
    <button class="admin-btn-primary" type="submit">{{ $submitLabel }}</button>
    <a href="{{ route('admin.users.index') }}" class="admin-btn-secondary">Back</a>
</div>
