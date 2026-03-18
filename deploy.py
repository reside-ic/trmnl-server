import hvac
import json
import os
from getpass import getpass

def vault_client():
    VAULT_ADDR = os.environ.get("VAULT_ADDR")
    if not VAULT_ADDR:
        VAULT_ADDR = getpass("Enter vault url, eg: https://example.com:8200")

    VAULT_TOKEN = os.environ.get("VAULT_AUTH_GITHUB_TOKEN")
    if not VAULT_TOKEN:
        VAULT_TOKEN = getpass("Enter your token for vault: ")

    client = hvac.Client(url=VAULT_ADDR, token=VAULT_TOKEN)
    client.auth.github.login(token=VAULT_TOKEN)    
    return client


def write_secrets(client):
    devs = client.secrets.kv.v1.list_secrets(mount_point="secret", 
                                             path="trmnl")
    keys = devs["data"]["keys"]
    devices = {}
    print(keys)
    for key in keys:
        dev = client.secrets.kv.v1.read_secret(mount_point="secret", 
                                               path=f"trmnl/{key}")
        data = dev["data"]
        if "mac" in data and "api_key" in data:
            devices[key] = {"mac": data["mac"], "api_key": data["api_key"]}

    with open("./secret/config.json", "w") as f:
        json.dump(devices, f, indent=2)


client=vault_client()
write_secrets(client)
