@forelse ($myclass as $sc)
    <tr>
        <td class="id" data-id="{{ $sc->id }}">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="chk_child">
                <label class="form-check-label"></label>
            </div>
        </td>
        <td class="schoolclass" data-schoolclass="{{ $sc->schoolclass }}">{{ $sc->schoolclass }}</td>
        <td class="schoolarm" data-schoolarm="{{ $sc->schoolarm }}">{{ $sc->schoolarm }}</td>
        <td class="term" data-term="{{ $sc->term }}">{{ $sc->term }}</td>
        <td class="session" data-session="{{ $sc->session }}">{{ $sc->session }}</td>
        <td>
            <ul class="d-flex gap-2 list-unstyled mb-0">
                @can('View my-class')
                    <!-- View Students Links for Terms 1, 2, 3 -->
                    <li>
                        <a href="{{ route('viewstudent', [$sc->schoolclassID, 1, $sc->sessionid]) }}" title="View Students in {{ $sc->schoolclass }} {{ $sc->schoolarm }} - First Term" class="btn btn-subtle-primary btn-icon"><i class="ph-eye"></i> Term 1</a>
                    </li>
                    <li>
                        <a href="{{ route('viewstudent', [$sc->schoolclassID, 2, $sc->sessionid]) }}" title="View Students in {{ $sc->schoolclass }} {{ $sc->schoolarm }} - Second Term" class="btn btn-subtle-primary btn-icon"><i class="ph-eye"></i> Term 2</a>
                    </li>
                    <li>
                        <a href="{{ route('viewstudent', [$sc->schoolclassID, 3, $sc->sessionid]) }}" title="View Students in {{ $sc->schoolclass }} {{ $sc->schoolarm }} - Third Term" class="btn btn-subtle-primary btn-icon"><i class="ph-eye"></i> Term 3</a>
                    </li>
                    <!-- Broadsheet Links for Terms 1, 2, 3 -->
                    <li>
                        <a href="{{ route('classbroadsheet', [$sc->schoolclassID, 1, $sc->sessionid]) }}" title="Broadsheet for {{ $sc->schoolclass }} {{ $sc->schoolarm }} - First Term" class="btn btn-subtle-success btn-icon"><i class="ph-eye"></i> Term 1</a>
                    </li>
                    <li>
                        <a href="{{ route('classbroadsheet', [$sc->schoolclassID, 2, $sc->sessionid]) }}" title="Broadsheet for {{ $sc->schoolclass }} {{ $sc->schoolarm }} - Second Term" class="btn btn-subtle-success btn-icon"><i class="ph-eye"></i> Term 2</a>
                    </li>
                    <li>
                        <a href="{{ route('classbroadsheet', [$sc->schoolclassID, 3, $sc->sessionid]) }}" title="Broadsheet for {{ $sc->schoolclass }} {{ $sc->schoolarm }} - Third Term" class="btn btn-subtle-success btn-icon"><i class="ph-eye"></i> Term 3</a>
                    </li>
                @endcan
                @can('Update my-class')
                    <li>
                        <a href="javascript:void(0);" class="btn btn-subtle-secondary btn-icon btn-sm edit-item-btn"><i class="ph-pencil"></i></a>
                    </li>
                @endcan
                @can('Delete my-class')
                    <li>
                        <a href="javascript:void(0);" class="btn btn-subtle-danger btn-icon btn-sm remove-item-btn"><i class="ph-trash"></i></a>
                    </li>
                @endcan
            </ul>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="6" class="noresult" style="display: block;">No results found</td>
    </tr>
@endforelse