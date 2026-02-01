param(
    [string] = (Get-Location),
    [int]$MaxDepth = 4,
    [int]$CurrentDepth = 0
)

function Show-DirectoryTree {
    param(
        [string]$Path,
        [int]$MaxDepth,
        [int]$CurrentDepth = 0
    )

    if ($CurrentDepth -gt $MaxDepth) {
        return
    }

    $indent = "  " * $CurrentDepth
    $items = @()
    
    try {
        $items = Get-ChildItem -Path $Path -ErrorAction SilentlyContinue | Sort-Object -Property @{Expression = {$_.PSIsContainer}; Descending = $true}, Name
    }
    catch {
        return
    }

    $itemCount = 0
    foreach ($item in $items) {
        $itemCount++
        $isLast = $itemCount -eq $items.Count
        $prefix = if ($isLast) { "└── " } else { "├── " }
        $icon = if ($item.PSIsContainer) { "📁 " } else { "📄 " }
        
        Write-Host "$indent$prefix$icon$($item.Name)"
        
        if ($item.PSIsContainer) {
            $nextIndent = "  " * ($CurrentDepth + 1)
            $extension = if ($isLast) { "    " } else { "│   " }
            Show-DirectoryTree -Path $item.FullName -MaxDepth $MaxDepth -CurrentDepth ($CurrentDepth + 1)
        }
    }
}

Write-Host "📂 Directory Map: $Path
"
Show-DirectoryTree -Path $Path -MaxDepth $MaxDepth
