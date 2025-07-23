@forelse ($classes as $sc)
    <tr>
        <td class="id" data-id="{{ $sc->schoolclassID }}">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="chk_child">
                <label class="form-check-label"></label>
            </div>
        </td>
        <td class="schoolclass" data-schoolclass="{{ $sc->schoolclass }}">{{ $sc->schoolclass }}</td>
        <td class="schoolarm" data-schoolarm="{{ $sc->schoolarm }}">{{ $sc->schoolarm }}</td>
        <td class="session" data-session="{{ $sc->session }}">{{ $sc->session }}</td>
        <td>
            <ul class="d-flex gap-2 list-unstyled mb-0">
                @can('View student-report')
                    <li>
                        <a href="{{ route('classbroadsheet', [$sc->schoolclassID, $sc->sessionid, 1]) }}" title="Broadsheet for {{ $sc->schoolclass }} {{ $sc->schoolarm }} - First Term" class="btn btn-subtle-success btn-icon"><i class="ph-eye"></i> 1st</a>
                    </li>
                    <li>
                        <a href="{{ route('classbroadsheet', [$sc->schoolclassID, $sc->sessionid, 2]) }}" title="Broadsheet for {{ $sc->schoolclass }} {{ $sc->schoolarm }} - Second Term" class="btn btn-subtle-success btn-icon"><i class="ph-eye"></i> 2nd</a>
                    </li>
                    <li>
                        <a href="{{ route('classbroadsheet', [$sc->schoolclassID, $sc->sessionid, 3]) }}" title="Broadsheet for {{ $sc->schoolclass }} {{ $sc->schoolarm }} - Third Term" class="btn btn-subtle-success btn-icon"><i class="ph-eye"></i> 3rd</a>
                    </li>
                @endcan
            </ul>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="5" class="noresult text-center">No classes found.</td>
    </tr>
@endforelse