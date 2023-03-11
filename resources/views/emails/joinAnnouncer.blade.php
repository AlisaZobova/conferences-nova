<div>
    Good afternoon, to the conference {{ $conference->title }} ({{ env("FRONTEND_URL") }}/conferences/{{ $conference->id }})
    joined a new member {{ $user->firstname . ' ' . $user->lastname }} with a report on the topic {{ $report->topic }}
    ({{ env("FRONTEND_URL") }}/reports/{{ $report->id }}) <br/><br/>
    Report time: {{ substr($report->start_time, 11, -3) }} - {{ substr($report->end_time, 11, -3) }}.
</div>
