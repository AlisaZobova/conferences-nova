<div>
    Good afternoon, to the conference {{ $conference->title }}
    ({{ env("FRONTEND_URL") }}/conferences/{{ $conference->id }})
    joined a new listener {{ $user->firstname . ' ' . $user->lastname}}.
</div>
