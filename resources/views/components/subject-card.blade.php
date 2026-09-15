@props(['subject'])
<a class="subject-card" href="{{ route('subjects.show',$subject) }}"><span class="subject-icon tone-{{ $subject->color }}"><x-preboard-icon :name="$subject->symbol"/></span><span><strong>{{ $subject->name }}</strong><small>{{ $subject->documents_count ?? $subject->documents()->published()->count() }} study resources</small></span><span class="subject-arrow">↗</span></a>
