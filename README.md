# Ηλεκτρονικός απουσιολόγος v3

Επικαιροποίηση του Ηλεκτρονικού απουσιολόγου v2 (https://github.com/g-theodoroy/apousiologos-v2).


## Σκοπός: 

- Η καταγραφή των απουσιών των μαθητών κάθε ώρα σε **πραγματικό χρόνο**. Άμεση **εποπτεία** των απόντων μαθητών από την 1η ώρα και κάθε ώρα.  **Εισαγωγή των απουσιών στο myschool** άμα τη λήξη των μαθημάτων (εξαγωγή αρχείου xls).

- **Ο προγραμματισμός** των διαγωνισμάτων

- **Η καταχώριση** της βαθμολογίας



## Εγκατάσταση



#### linux terminal

```
git clone https://github.com/g-theodoroy/apousiologos-v3.git
cd apousiologos-v3
composer install --no-dev
chmod -R 0777 storage/

```


#### windows

Κατεβάστε το zip:

https://github.com/g-theodoroy/apousiologos-v3/archive/refs/heads/main.zip

Αποσυμπιέστε και με το **cmd** πηγαίνετε στον φάκελο και τρέξτε:
```
composer install --no-dev
```


Φυσικά θα πρέπει να έχετε εγκατεστημένη την **php** και τον **composer**



Περισσότερες πληροφορίες για την εγκατάσταση θα βρείτε στον παρακάτω σύνδεσμο. Θα πρέπει να αλλάξετε αντίστοιχα τα ονόματα ( protocol <=> apousiologos-v3) :

https://github.com/g-theodoroy/electronic_protocol/



Αν θέλετε να ρυθμίσετε αποστολή **email** συμπληρώστε κατάλληλα στο αρχείο **.env**:

```
MAIL_MAILER=smtp
MAIL_HOST=xxxxxxxxxx
MAIL_PORT=587
MAIL_USERNAME=xxxxxxxxxx
MAIL_PASSWORD=xxxxxxxxxx
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=xxxxxxxxxx
MAIL_FROM_NAME="${APP_NAME}"
```


## Ρύθμιση - χρήση

[Οδηγίες ρύθμισης κ χρήσης Ηλ.Απουσιολόγου.pdf](https://drive.google.com/file/d/1TrRDgVu3BTsZcATAHILY4VXC6cUVIcmP/view?usp=sharing)


# GΘ@ΙΟΥΛ_2025



