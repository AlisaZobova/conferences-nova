<div>
    Good afternoon, at the conference {{ $conference->title }}
    ({{ env("FRONTEND_URL") }}/conferences/{{ $conference->id }})
    your report {{ $originalTopic }}
    ({{ env("FRONTEND_URL") }}/reports/{{ $report->id }})
    has been updated by the administration.
</div>
