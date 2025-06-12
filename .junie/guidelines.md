Rules you have to stick to and notes you need to be aware of:

# Project middleware registration file:

bootstrap/app.php

It is not Kernel.php. Kernel.php exists in older Laravel versions. Mine is Laravel 12.

# AI Assistance Guidelines

- Whenever I give you a task, first try to guess what type of task it is and then follow the rules outlined below accordingly, do this each and every time:

## Task: General Coding & Implementation
- Always make code change suggestions in "change this, to this" format. Meaning, always, explicitly show, exactly which
- lines of code that need to be changed, and then what they should be changed to.
- Nothing more nothing less.
- Let's not touch anything else while doing this. I need surgical attention.
- Stick to what exactly I am asking and do not propose optimizations unless I explicitly ask for.
- Make sure to give me only the part of the file where I need to make changes and/or additions (in, change this to this format), not the whole file.

## Task: Brainstorming
- How do we approach this? Don't produce code, let's first discuss, brainstorm, debug, understand the full context.
- Don't just jump ahead. I don't want to write code before we are 100% sure what we are doing.
- Let's have an analytical conversation with a purpose of getting closer to a good solution in mind.

## Task: Code Understanding
- Right now, the only thing I am trying to do is to understand the code, not make changes.
- Do not propose changes in your response.
- Let's focus on understanding. I will explicitly tell you when I want to make changes.

## Task: Debugging
- Stick to what exactly I am asking and do not propose optimizations unless I explicitly ask for.
- Make sure to give me only the part of the file where I need to make changes and/or additions, not the whole file.
- When debugging, please give me ONLY ONE thing to try and wait for my response before suggesting something else.

# Minimalist Guidance Principle

- Provide ONE simple, clear solution first
- Only offer alternatives when explicitly asked
- Keep explanations brief and focused
- Prioritize clarity over comprehensiveness for junior developers


