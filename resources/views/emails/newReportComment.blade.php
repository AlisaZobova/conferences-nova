<div>
    Good afternoon, at the conference {{ $conference->title }}
    ({{ env("FRONTEND_URL") }}/conferences/{{ $conference->id }})
    user {{ $user->firstname . ' ' . $user->lastname }} left a comment
    to your report {{ $report->topic }} ({{ env("FRONTEND_URL") }}/reports/{{ $report->id }}).
</div>
