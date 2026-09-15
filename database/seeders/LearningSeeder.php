<?php

namespace Database\Seeders;

use App\Models\Chapter;
use App\Models\Exam;
use App\Models\StudyDocument;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LearningSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = [
            ['name' => 'Physics', 'color' => 'lilac', 'symbol' => 'bolt', 'description' => 'Explore the ideas behind electricity, motion, and the physical world.'],
            ['name' => 'Biology', 'color' => 'mint', 'symbol' => 'beaker', 'description' => 'Connect the building blocks of life, from cells to inheritance.'],
            ['name' => 'Mathematics', 'color' => 'sand', 'symbol' => 'variable', 'description' => 'Build confidence with methods, patterns, and worked examples.'],
        ];
        foreach ($subjects as $s) {
            Subject::firstOrCreate(['slug' => Str::slug($s['name'])], $s);
        }
        $notes = [
            ['Physics', 'Current electricity', 'Electric circuits, made clearer', 'Understand current, resistance, and the relationship that connects them.', 'notes',
                "# Electric circuits, made clearer\n\n## Start with the idea\nElectric current is the rate of flow of electric charge. If a charge Q passes a point in time t, the average current is **I = Q / t**. Current is measured in amperes (A), charge in coulombs (C), and time in seconds (s).\n\nPotential difference describes the energy transferred per unit charge. Resistance describes how strongly a component opposes current.\n\n## Ohm’s law\nFor an ohmic conductor at constant temperature, **V = IR**. Here V is potential difference in volts, I is current in amperes, and R is resistance in ohms. This relationship is not universal for every electrical component.\n\n> A 6 Ω resistor carries a current of 2 A. The potential difference is V = 2 × 6 = **12 V**.\n\n## A reliable method\n1. List the known quantities.\n2. Convert units before substituting.\n3. Rearrange the equation for the unknown.\n4. Include the correct unit in the answer.\n\n## Check your understanding\nA 10 Ω resistor carries 0.5 A. The potential difference is **5 V**. Doubling the current through an unchanged ohmic resistance doubles the potential difference.\n\n## Common mistake\nDo not confuse current with voltage. Current is charge flow per unit time; voltage is energy transferred per unit charge."],
            ['Physics', 'Current electricity', 'Series and parallel: see the difference', 'A practical guide to combining resistors and checking your answer.', 'worked_examples',
                "# Series and parallel resistors\n\n## Resistors in series\nIn a single unbranched path, the current is the same through each resistor. The equivalent resistance is the sum: **R = R₁ + R₂ + …**.\n\n> A 3 Ω and a 6 Ω resistor in series have an equivalent resistance of **9 Ω**.\n\n## Resistors in parallel\nParallel branches share the same potential difference. Their reciprocal resistances add: **1/R = 1/R₁ + 1/R₂ + …**.\n\nFor two resistors, **R = R₁R₂ / (R₁ + R₂)**.\n\n> A 3 Ω and a 6 Ω resistor in parallel give R = 18 / 9 = **2 Ω**.\n\n## Sense-check the result\n- A series equivalent is greater than either positive component resistance.\n- A parallel equivalent is less than the smallest positive component resistance.\n- Draw the circuit before deciding whether components are in series or parallel.\n\n## Try it\nTwo 4 Ω resistors in parallel have an equivalent resistance of **2 Ω**. In series their equivalent resistance is **8 Ω**."],
            ['Physics', 'Energy and power', 'Electrical power without the guesswork', 'Connect energy, time, voltage and current through worked examples.', 'revision_guide',
                "# Electrical energy and power\n\n## What power measures\nPower is the rate of energy transfer: **P = E / t**. One watt is one joule per second. In a circuit, **P = VI**. For a resistor, substitution using Ohm’s law also gives **P = I²R = V²/R**.\n\n## Worked example\nA device operates at 12 V and draws 2 A. Its power is **24 W**. If it runs for 10 seconds at this power, the energy transferred is E = Pt = 24 × 10 = **240 J**.\n\n## Units matter\nA kilowatt-hour is a unit of energy, not power. **1 kWh = 3.6 million joules**. A 1 kW device running for 2 hours transfers 2 kWh of energy.\n\n## Revision checklist\n- Use seconds when calculating joules from watts.\n- Use hours when calculating kWh from kW.\n- Check whether power is constant over the interval.\n- Include a unit on every calculated result."],
            ['Biology', 'Genetics', 'Inheritance: the essential ideas', 'Make sense of genes, alleles, genotype and phenotype.', 'notes',
                "# Inheritance: the essential ideas\n\n## Genes and alleles\nA gene is a region of DNA that contributes to a functional product. Different forms of a gene are called **alleles**. In a simplified diploid example, an organism carries two alleles at a particular locus, one inherited from each parent.\n\n## Genotype and phenotype\nThe **genotype** describes the alleles present. The **phenotype** describes an observable characteristic influenced by genotype and environment. These words describe related but different things.\n\n## A simple dominant–recessive model\nLet A be a dominant allele and a a recessive allele. AA and Aa show the dominant phenotype in this simple model; aa shows the recessive phenotype. **Homozygous** means two identical alleles. **Heterozygous** means two different alleles.\n\n> Dominant does not mean more common, stronger, or better. It describes the relationship between alleles in a heterozygote.\n\n## Remember the limits\nMany traits involve multiple genes, environmental effects, incomplete dominance or other relationships. The simple model is a starting point, not a description of all inheritance."],
            ['Biology', 'Genetics', 'A Punnett square, step by step', 'Use a simple genetic cross to reason about probabilities.', 'worked_examples',
                "# A Punnett square, step by step\n\n## The cross\nConsider a single-gene cross **Aa × Aa** under a complete-dominance model. Each parent can contribute A or a with equal probability.\n\n## Combine the possibilities\nThe four equally likely combinations are **AA, Aa, Aa, aa**. The genotype ratio is **1:2:1**. If A is completely dominant, the expected phenotype ratio is **3:1**.\n\n## What the result means\nThe probability of aa for each offspring is **1/4**. This does not guarantee that every group of four offspring contains exactly one aa individual. Probability describes expectations across repeated events.\n\n## A checking routine\n1. Write each parent’s genotype.\n2. Identify possible gametes.\n3. Combine one allele from each parent.\n4. Count genotypes before converting to phenotypes.\n\n## Common error\nDo not treat all heterozygotes as a separate phenotype unless the problem states a model such as incomplete dominance."],
            ['Biology', 'Cell biology', 'Inside a cell: structure and function', 'Link key cell structures to the jobs they perform.', 'revision_guide',
                "# Inside a cell\n\n## Start with function\nCell structures work together. In a typical eukaryotic cell, the **nucleus** contains most of the genetic material. **Ribosomes** synthesize proteins. **Mitochondria** participate in aerobic energy metabolism.\n\n## Boundaries and transport\nThe plasma membrane regulates movement between the cell and its environment. **Diffusion** is net movement down a concentration gradient. **Osmosis** concerns net water movement across a selectively permeable membrane. Active transport requires energy to move substances against an electrochemical gradient.\n\n## Plant-cell features\nPlant cells typically have a cellulose cell wall and a large central vacuole. Photosynthetic plant cells contain chloroplasts. Not every plant cell contains chloroplasts: many root cells do not.\n\n## Retrieval practice\nClose your notes and describe one function each for the nucleus, ribosome, membrane and mitochondrion. Then check whether your explanation distinguishes the structure from its function."],
            ['Mathematics', 'Differentiation', 'Differentiation, step by step', 'See how the power rule turns change into something you can calculate.', 'worked_examples',
                "# Differentiation, step by step\n\n## The idea\nA derivative describes the instantaneous rate of change of a function. Graphically, it gives the gradient of a tangent where the derivative exists.\n\n## The power rule\nFor a power function **f(x) = xⁿ**, the derivative is **f′(x) = nxⁿ⁻¹** wherever the rule applies. A constant has derivative zero.\n\n> If f(x) = x³, then f′(x) = 3x². At x = 2, the derivative is **12**.\n\n## Work through a polynomial\nLet y = 3x² + 2x − 7. Differentiate each term: **dy/dx = 6x + 2**. The constant −7 contributes zero.\n\n## A useful distinction\nThe value of f(2) and the value of f′(2) answer different questions. One is the function’s height; the other is its local rate of change.\n\n## Try it\nFor y = 4x³ − 5x + 1, the derivative is **12x² − 5**."],
            ['Mathematics', 'Integration', 'Integration: build the idea back up', 'Learn the reverse power rule and why the constant matters.', 'notes',
                "# Integration: build the idea back up\n\n## Antiderivatives\nAn antiderivative F of f satisfies **F′(x) = f(x)** on an interval. Indefinite integration gives a family of antiderivatives.\n\n## Reverse power rule\nFor n ≠ −1, the integral of xⁿ is **xⁿ⁺¹ / (n + 1) + C**, on an interval where the expression is defined. The case n = −1 needs a different rule.\n\n> The integral of 3x² is **x³ + C**. Differentiate your answer to check it.\n\n## Why include C?\nThe derivative of any constant is zero. Both x³ + 2 and x³ − 8 differentiate to 3x², so the indefinite integral must allow for an arbitrary constant.\n\n## Definite integrals\nIf F is an appropriate antiderivative, evaluate from a to b using **F(b) − F(a)**. The definite integral gives signed accumulation; interpreting it as geometric area requires care when the function is negative.\n\n## Check your work\nDifferentiate the antiderivative. This catches many power and coefficient errors."],
            ['Mathematics', 'Probability', 'Probability: reason before you calculate', 'Use sample spaces, complements and independence carefully.', 'revision_guide',
                "# Probability: reason before you calculate\n\n## Describe the experiment\nA sample space contains possible outcomes. For finitely many equally likely outcomes, **P(A) = number of outcomes in A / total number of outcomes**.\n\n## The complement\nThe probability that A does not occur is **1 − P(A)**. For a fair six-sided die, the probability of not rolling a six is **5/6**.\n\n## Independent events\nIf A and B are independent, **P(A and B) = P(A)P(B)**. Two independent fair coin tosses have probability **1/4** of both being heads.\n\n## Do not confuse independence and exclusion\nMutually exclusive events cannot happen together in the same trial. Independent events do not change each other’s probability. Nonzero mutually exclusive events are not independent.\n\n## A checking habit\nAlways state your assumptions: Is the die fair? Is sampling with replacement? Are trials independent? The formula is useful only when its conditions hold."],
        ];
        foreach ($notes as [$subjectName,$chapterTitle,$title,$description,$type,$body]) {
            $subject = Subject::where('name', $subjectName)->firstOrFail();
            $chapter = Chapter::firstOrCreate(['subject_id' => $subject->id, 'title' => $chapterTitle], ['position' => $subject->chapters()->count() + 1]);
            StudyDocument::firstOrCreate(['slug' => Str::slug($title)], ['chapter_id' => $chapter->id, 'title' => $title, 'description' => $description, 'type' => $type, 'preview' => Str::before($body, '##'), 'body' => $body, 'rights_statement' => 'Original foundation material created for this application. Formal curriculum review is pending.', 'status' => 'published', 'is_free' => true, 'reading_minutes' => max(3, (int) ceil(str_word_count($body) / 150))]);
        }
        $sets = [
            'Physics' => ['Electricity essentials', [
                ['A 6 Ω resistor carries 2 A. What is the potential difference?', ['3 V', '8 V', '12 V', '24 V'], 2, 'V = IR = 2 × 6 = 12 V.'],
                ['What is the equivalent resistance of 3 Ω and 6 Ω in series?', ['2 Ω', '3 Ω', '6 Ω', '9 Ω'], 3, 'Series resistances add: 3 + 6 = 9 Ω.'],
                ['A device operates at 12 V and 2 A. What is its power?', ['6 W', '14 W', '24 W', '48 W'], 2, 'Electrical power P = VI = 12 × 2 = 24 W.'],
                ['Which quantity is measured in coulombs?', ['Current', 'Charge', 'Power', 'Resistance'], 1, 'A coulomb is the SI unit of electric charge.'],
                ['Two 4 Ω resistors are connected in parallel. Their equivalent resistance is:', ['2 Ω', '4 Ω', '8 Ω', '16 Ω'], 0, 'For equal parallel resistors, the equivalent resistance is half of either resistance: 2 Ω.'],
            ]],
            'Biology' => ['Cells & inheritance foundations', [
                ['Different forms of a gene are called:', ['Tissues', 'Organs', 'Alleles', 'Cells'], 2, 'Alleles are alternative forms of a gene.'],
                ['Which term describes an organism’s allele combination at a locus?', ['Phenotype', 'Genotype', 'Ecosystem', 'Population'], 1, 'Genotype describes the alleles present; phenotype describes an observable characteristic.'],
                ['In an Aa × Aa cross, what is the probability of aa?', ['1/2', '1/4', '3/4', '1'], 1, 'The equally likely combinations are AA, Aa, Aa and aa, so aa has probability 1/4.'],
                ['Which structure synthesizes proteins?', ['Ribosome', 'Cell wall', 'Vacuole', 'Chloroplast membrane'], 0, 'Ribosomes are the sites of protein synthesis.'],
                ['Which process describes net movement down a concentration gradient?', ['Active transport', 'Mitosis', 'Diffusion', 'Translation'], 2, 'Diffusion is net movement down a concentration gradient.'],
            ]],
            'Mathematics' => ['Calculus & probability foundations', [
                ['What is the derivative of x³?', ['x²', '3x²', '3x', 'x⁴/4'], 1, 'The power rule gives d(x³)/dx = 3x².'],
                ['What is the derivative of a constant?', ['1', 'The same constant', 'x', '0'], 3, 'A constant does not change with x, so its derivative is zero.'],
                ['An antiderivative of 2x is:', ['x²', '2', '2x²', 'x³'], 0, 'Differentiating x² gives 2x. The indefinite integral is x² + C.'],
                ['For a fair six-sided die, the probability of not rolling a six is:', ['1/6', '1/2', '5/6', '1'], 2, 'The complement of rolling six is 1 − 1/6 = 5/6.'],
                ['Two independent fair coin tosses both show heads with probability:', ['1/2', '1/4', '3/4', '1'], 1, 'Multiply the independent probabilities: 1/2 × 1/2 = 1/4.'],
            ]],
        ];
        foreach ($sets as $name => [$title,$questions]) {
            Exam::firstOrCreate(['slug' => Str::slug($title)], ['subject_id' => Subject::where('name', $name)->value('id'), 'title' => $title, 'description' => 'A focused five-question check with explanations to help you build a strong foundation.', 'duration_minutes' => 10, 'status' => 'published', 'questions' => array_map(fn ($q) => ['prompt' => $q[0], 'options' => $q[1], 'correct' => $q[2], 'explanation' => $q[3]], $questions)]);
        }
        if (app()->environment('local') && config('preboard.demo_enabled')) {
            $user = User::firstOrCreate(['email' => 'student@preboard.test'], ['name' => 'Demo Student', 'password' => Str::random(40)]);
            if (! $user->email_verified_at) {
                $user->forceFill(['email_verified_at' => now()])->save();
            }
        }
    }
}
