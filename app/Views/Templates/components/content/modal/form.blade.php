@props([
    "method" => "post",
    "action" => ""
])

<form hx-{{ $method }}="{{ $action }}"
      hx-target="#main-page-modal .modal-box-content"
      hx-indicator=".modal-content-loader"
      {{ $attributes->merge(["class"=> "min-w-80"]) }} >

      {{ $slot }}

</form>
