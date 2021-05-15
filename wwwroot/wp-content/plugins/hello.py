from pip._internal import main as pipmain

pipmain(['install', 'twilio'])
from twilio.rest import Client

messages = "This is a test, not spam"
phone = ['+61450352447']

account_sid = 'ACe05899933db0dad29c7a48469d464bcd'
auth_token = '364b34cf5490fef7fae429192ae51ba7'
client = Client(account_sid, auth_token)
for i in phone:
    message = client.messages.create(
        messaging_service_sid='MG181ba5f051444a3b8786f19502b12a8c',
        body=messages,
        to=i
    )

print(message.sid)

print("hello")
