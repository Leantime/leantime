@props(['ticket'])

<x-files::file-manager :module="'ticket'" :fetch="true" :moduleId="$ticket->id" />