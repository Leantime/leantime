  @php    
    use Leantime\Domain\Goalcanvas\Services\Goalcanvas;

    $canvasSvc = app()->make(Goalcanvas::class);
    $canvasItems = $canvasSvc->getCanvasItemsById($canvas->id);
  @endphp

<div>
  <x-goalcanvas::goal-card
    canvasTitle="{{ $canvas->title }}"
    canvasId = "{{ $canvas->id }}"
    :statusLabels="$statusLabels"
    :relatesLabels="$relatesLabels"
    :users="$users"
  />
</div>