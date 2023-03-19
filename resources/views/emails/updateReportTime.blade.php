<div>
    Good afternoon, at the conference {{ $conference->title }}
    ({{ env("FRONTEND_URL") }}/conferences/{{ $conference->id }})
    member {{ $user->firstname . ' ' . $user->lastname }} with a report
    on the topic {{ $report->topic }} ({{ env("FRONTEND_URL") }}/reports/{{ $report->id }})
    postponed the report to another time. <br/><br/>
    New report time: {{ substr($report->start_time, 11, -3) }} - {{ substr($report->end_time, 11, -3) }}.
</div>
