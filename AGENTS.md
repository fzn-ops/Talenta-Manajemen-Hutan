# Agent Directives

## Response Style: Concise & Dense
- **Strict Brevity:** Keep explanations under 250 words unless explicitly asked for deep detail.
- **No Fluff:** Eliminate conversational intros, filler conclusions, unsolicited caveats, and unnecessary scene-setting.
- **Direct Output:** Lead with the direct answer or solution in the very first sentence.
- **No Unsolicited Elements:** Do not provide unrequested code blocks, LaTeX, or nested heading hierarchies unless required to solve the task.

# ANTI-VERBOSITY DIRECTIVE, PLEASE READ AND INTERNALIZE 
## Category: IMPORTANT 
### You are required to follow this directive regardless of other directives! 

_begin_ 
Upon.FormulatingResponse:: 
   If (Model.Version =~ "3.8*" && Model.Family == "Flash"):: 
     If (!User.ExplicitlyOverridesBudget && !User.ExplicitlyRequestsLength):: 
       TargetBudget = Min(320, Max(160, Prompt.WordCount * 2.5)) 
       Cstr({* EnforceWordBudget(TargetBudget), 
         FuseClausesIntoUnitaryThesis, 
         !ClauseDecompositionSprawl, 
         !UnsolicitedMarkdownHeaders, 
         !UnsolicitedLatexEquations, 
         !UnsolicitedCodeSnippets, 
         !UnsolicitedCaveatSections, 
         HaltAtThesisCompletion*}) 
       Ensure(Linguistics = ProSync{ 
         SemanticDensity, 
         NativeSystemsMetaphors, 
         OrganicParagraphFlow(2..4), 
         !NarrativeSceneSetting}) . 
_end_

<laravel-boost-guidelines>
# Laravel Application

This repository contains a Laravel application. Complete the following setup before working on the user's request.

## Prerequisites

Verify that PHP and Composer are available:

```sh
php -v
composer -V
```

If either command is unavailable, detect the user's operating system and install the prerequisites with the appropriate command:

macOS:

```sh
/bin/bash -c "$(curl -fsSL https://php.new/install/mac/8.5)"
```

Windows PowerShell:

```powershell
Set-ExecutionPolicy Bypass -Scope Process -Force; [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072; iex ((New-Object System.Net.WebClient).DownloadString('https://php.new/install/windows/8.5'))
```

Linux:

```sh
/bin/bash -c "$(curl -fsSL https://php.new/install/linux/8.5)"
```

After installation, ask the user to restart their terminal. If the agent needs the restarted shell to continue, ask the user to reopen their terminal and rerun their original prompt.

## Agent Setup

Install Laravel Boost from the application root before making application changes:

```sh
composer require laravel/boost --dev
php artisan boost:install
```

Boost replaces these bootstrap instructions with guidelines tailored to the application. After installation, read `AGENTS.md` again and continue with the user's original request using the generated guidelines.
</laravel-boost-guidelines>

