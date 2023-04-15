using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using System.IO;
using System.Diagnostics;

namespace Algorithms
{
    public class Program
    {
        static void Main(string[] args)
        {
            if (args.Length != 2)
            {
                Console.WriteLine("Необходимо передать 2 аргумента через командную строку!");
                Console.ReadKey();
                return;
            }

            int Capital;
            string InputsPath = args[0], OutputsPath = args[1];
            SetOfShares Portfolio = GetInputs(InputsPath, out Capital); 

            if (Portfolio.Length == 0)
            {
                Console.WriteLine("Ошибка");
                Console.ReadKey();
                return;
            }

            Portfolio.Print();

            Console.WriteLine("Вычисляю лучший набор из " + Portfolio.Total() + " возможных. Ждите...\n");
            System.Diagnostics.Stopwatch SW = new Stopwatch();
            SW.Start();
            SetOfShares Best = Portfolio.TheBest(Capital);
            SW.Stop();

            Console.Write("Лучшее подмножество: ");
            Best.PrintCounts();
            Console.WriteLine("\nОбщая стоимость: " + Best.SummaryCost());
            Console.WriteLine("Мат. ожидание прибыли: " + Best.SummaryExpectation());
            Console.WriteLine("\nСлучаев:\nВсего: " + Portfolio.Total() + "\nПросм: " + SetOfShares.Enumerated + "\nВремя: {0} сек",  SW.ElapsedMilliseconds / 1000.0);
            Console.WriteLine("\nНажмите любую клавишу...");
            Console.ReadKey();

            Save(OutputsPath, Best);
        }

        public static SetOfShares GetInputs(string path, out int Capital)
        {
            LinkedList<Share> ListPortfolio = new LinkedList<Share>();
            StreamReader Inputs = new StreamReader(path, System.Text.Encoding.Default);

            Capital = Int32.Parse(Inputs.ReadLine());
            while (!Inputs.EndOfStream)
            {
                string name = Inputs.ReadLine();
                int cost    = Int32.Parse(Inputs.ReadLine());
                int income  = Int32.Parse(Inputs.ReadLine());
                int odds    = Int32.Parse(Inputs.ReadLine());
                int count   = Int32.Parse(Inputs.ReadLine());

                if ((odds < 10) || (income <= cost) || (cost > Capital) || (count <= 0)) continue; // Бритва входа
                float expectation = (income - cost) * odds / 100;
                ListPortfolio.AddLast(new Share(name, cost, count, expectation));
            }

            Share[] Shares = ListPortfolio.ToArray();
            return new SetOfShares(Shares);
        }

        static void Save(string Path, SetOfShares Set)
        {
            DateTime curDate = DateTime.Now;
            File.WriteAllText(Path, curDate.ToString() + "\r\n\r\n");

            for (int i = 0; i < Set.Length; i++)
            {
                if (Set.Shares[i].Count == 0) continue;
                string report = string.Format("{0} - {1} штук\r\n", Set.Shares[i].Name, Set.Shares[i].Count);
                File.AppendAllText(Path, report);
            }
        }
    }

    public struct Share : IComparable<Share>
    {
        public string Name { get; set; }
        public int Cost { get; set; }
        public int Count { get; set; }
        public float Expectation { get; set; }

      // --------------------------------------------

        public Share(string name, int cost, int count, float expectation) : this()
        {
            Name        = name;
            Cost        = cost;
            Count       = count;
            Expectation = expectation;
        }

        public int CompareTo(Share obj) // Сортировка от дорогих до дешёвых
        {
            if (this.Cost > obj.Cost)
                return -1;
            if (this.Cost < obj.Cost)
                return 1;
            else
                return 0;
        }

        public float FullExpectation()
        {
            return Expectation * Count;
        }

        public void Print()
        {
            Console.WriteLine(Name);
            Console.WriteLine("Cost: " + Cost);
            Console.WriteLine("Count: " + Count);
            Console.WriteLine("Expectation: " + Expectation + "\n");
        }
    }

    public class SetOfShares
    {
        public Share[] Shares { get; private set; }
        public int Length     { get; private set; }
        public static int Enumerated = 0; // Сколько наборов было рассмотрено. Можно убрать

        //---------------------------------------

        public SetOfShares(Share[] shares)
        {
            Array.Sort(shares);
            Shares = shares;
            Length = shares.Length;
        }

        public bool CanBeBought(int Capital) // Можно ли купить данный набор акций с нашим капиталом
        {
            return Capital >= SummaryCost();
        }

        public int SummaryCost()
        {
            int SummaryCost = 0;
            foreach (Share item in Shares)
            {
                SummaryCost += item.Cost * item.Count;
            }
            return SummaryCost;
        }

        public float SummaryExpectation() // Используется как оценка ветви сверху и для подсчёта прибыли с набора акций
        {
            float result = 0;
            foreach (Share item in Shares)
            {
                result += item.Expectation * item.Count;
            }
            return result;
        }

        public int Total() // Количество возможных наборов
        {
            int total = 1;
            foreach (Share item in Shares)
            {
                total *= item.Count + 1;
            }
            return total;
        }

        public SetOfShares TheBest(int Capital) // Подготовка перед входом в рекурсивную функцию
        {
            Enumerated = 0;
            Array.Sort(Shares); // Сортировка от дорогих до дешёвых

            Share[] BestArray = new Share[Length];  // Создаём набор для хранения рекорда. Всё как во входе, только количество акций по нулям
            Share[] RootArray = new Share[Length];  // Создаём корневой набор, тоже пустой
            Array.Copy(this.Shares, BestArray, Length); // Копируем все данные
            Array.Copy(this.Shares, RootArray, Length);
            for (int i = 0; i < Length; i++)
            {
		        BestArray[i].Count = 0; // Количества ставим нулевые, пустой же
                RootArray[i].Count = 0;
            }
            SetOfShares Best = new SetOfShares(BestArray); // Инициализируем объекты наборов
            SetOfShares Root = new SetOfShares(RootArray);

            if (Length == 0) return Best; // Подан пустой набор

            // Создаём массив с оценками сверху.
            float[] EstimateArray = new float[Length];
            EstimateArray[Length - 1] = Shares[Length - 1].Expectation * Shares[Length - 1].Count;
            for (int i = Length - 2; i >= 0; i--)
            {
                EstimateArray[i] = Shares[i].FullExpectation() + EstimateArray[i+1];
            }

            FindTheBest(Root, this, Capital, ref Best, EstimateArray);
         // В функцию отправляются: набор но без акций (корень дерева обхода); полный набор акций; Кол-во наших денег; Рекорд и массив с оценками (чтоб без глобалок);

            return Best;
        }

        private void FindTheBest(SetOfShares Parent, SetOfShares Original, int Capital, ref SetOfShares Best, float[] EstimateArray, int Index = 0)
        {
            if (!Parent.CanBeBought(Capital)) return; // Нельзя купить? Давай до свидания.

            bool All_Children_Are_Unavailable; // Узнаем - можно ли хоть что-нибудь добавить вообще?
            // В конце самые дешёвые акции, значит если нельзя купить последнюю (самую дешёвую), то и на другие денег не хватит
            SetOfShares LastAddition = Parent.Addition(Original, Length - 1);
            if (LastAddition != null)
                All_Children_Are_Unavailable = !LastAddition.CanBeBought(Capital);
            else
                All_Children_Are_Unavailable = true; // indeх максимален и равен Length

            if (All_Children_Are_Unavailable) // Никто из детей не может быть куплен - текущий набор может быть рекордом
                if (Best.SummaryExpectation() < Parent.SummaryExpectation()) // А не рекорд ли это часом?
                {
                    Best = Parent;
                    return;
                }

            for (int index = Index; index < Length; index++) // Есть доступные дети. Копаем глубже.
            {
                SetOfShares Child = Parent.Addition(Original, index); // Получаем добавление
                if (Child == null) continue; // Недоступный ребёнок попался

                // Считаем сумму ожиданий неприкасаемых элементов от 0 до index и прибавляем индексовый элемент оценочного массива
                float Estimate = EstimateArray[index];
                for (int j = 0; j < index; j++)
                {
                    Estimate += Parent.Shares[j].FullExpectation();
                }

                if (Best.SummaryExpectation() < Estimate) // Оценивание
                    FindTheBest(Child, Original, Capital, ref Best, EstimateArray, index);
            }
        }

        public SetOfShares Addition(SetOfShares Original, int Index) // Добавляет одну акцию к акции под номером index
        {
            Enumerated++;
            if (Shares[Index].Count < Original.Shares[Index].Count)
            {
                Share[] New = new Share[Length];
                Array.Copy(Shares, New, Length);
                New[Index].Count++;
                return new SetOfShares(New);
            }
            else
                return null;
        }

        public void Print()
        {
            foreach (Share item in Shares)
            {
                item.Print();
            }
        }

        public void PrintCounts()
        {
            foreach (Share item in Shares)
            {
                Console.Write(item.Count + " ");
            }
        }

        public bool Equals(SetOfShares Second)
        {
            return this.Shares.SequenceEqual(Second.Shares);
        }
    }
}